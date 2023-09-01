<?php

declare(strict_types=1);

namespace App\AuditLog;

use App\AuditLog\Entity\AuditLog as AuditLogEntity;
use App\AuditLog\Entity\AuditLogCollectionInsert;
use App\AuditLog\Entity\AuditLogCollectionRemove;
use App\AuditLog\Entity\AuditLogEntity as AuditLogEntityEntity;
use App\AuditLog\Entity\AuditLogFieldCreate;
use App\AuditLog\Entity\AuditLogFieldUpdate;
use App\AuditLog\Entity\AuditLogProperty;
use App\AuditLog\Event\PropertyEvent;
use App\AuditLog\Event\PropertyEventArgs;
use App\AuditLog\Repository\AuditLogEntityRepository;
use App\DateTimeProvider\DateTimeProvider;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class AuditLog
{
    private bool $requiresFlush = false;
    private array $createCache = [];

    /**
     * @var array<string, AuditLogEntityEntity>
     */
    private array $auditLogEntityCache = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AuditLogEntityRepository $auditLogEntityRepository,
        private readonly AuditLogFactory $factory,
        private readonly DateTimeProvider $dateTimeProvider,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function scheduleForCreate(object $entity): void
    {
        $this->createCache[spl_object_id($entity)] = $entity;
    }

    public function isScheduleForCreate(object $entity): bool
    {
        return array_key_exists(spl_object_id($entity), $this->createCache);
    }

    public function logCreate(object $entity): void
    {
        $log = $this->createAuditLog($entity, EntityModeEnum::Create);

        foreach ($this->getEntityFieldValues($entity) as $fieldName => $value)
        {
            $field = $this->createAuditLogFieldCreate($fieldName, $value);
            $field->setAuditLog($log);
            $this->entityManager->persist($field);
        }

        unset($this->createCache[spl_object_id($entity)]);
        $this->entityManager->persist($log);

        $this->handleAuditLogPropertyEvent(PropertyEvent::ON_CREATE, $log, $entity);

        $this->requireFlush();
    }

    public function logUpdate(object $entity, array $fields): void
    {
        $log = $this->createAuditLog($entity, EntityModeEnum::Update);

        foreach ($fields as $fieldName => [$oldValue, $newValue])
        {
            $field = $this->createAuditLogFieldUpdate($fieldName, $newValue, $oldValue);
            $field->setAuditLog($log);
            $this->entityManager->persist($field);
        }

        $this->entityManager->persist($log);

        $this->handleAuditLogPropertyEvent(PropertyEvent::ON_UPDATE, $log, $entity);

        $this->requireFlush();
    }

    public function logRemove(object $entity): void
    {
        $log = $this->createAuditLog($entity, EntityModeEnum::Delete);

        $this->entityManager->persist($log);

        $this->handleAuditLogPropertyEvent(PropertyEvent::ON_REMOVE, $log, $entity);

        $this->requireFlush();
    }

    private array $scheduledCollectionInserts = [];
    private array $scheduledCollectionRemovals = [];

    public function hasCollectionUpdatesScheduled(): bool
    {
        return count($this->scheduledCollectionInserts) > 0
            || count($this->scheduledCollectionRemovals) > 0;
    }

    public function scheduleForCollectionInsert(object $ownerEntity, string $ownerField, object $collectionItem): void
    {
        $this->scheduledCollectionInserts[] = [$ownerEntity, $ownerField, $collectionItem];
    }

    public function scheduleForCollectionRemoval(object $ownerEntity, string $ownerField, object $collectionItem): void
    {
        $this->scheduledCollectionRemovals[] = [$ownerEntity, $ownerField, $collectionItem];
    }

    public function handleCollectionUpdatesSchedule(): void
    {
        if (count($this->scheduledCollectionInserts))
        {
            foreach ($this->scheduledCollectionInserts as [$ownerEntity, $ownerField, $collectionItem])
            {
                $this->logCollectionInsert($ownerEntity, $ownerField, $collectionItem);
            }

            $this->scheduledCollectionInserts = [];

            $this->requireFlush();
        }

        if (count($this->scheduledCollectionRemovals))
        {
            foreach ($this->scheduledCollectionRemovals as [$ownerEntity, $ownerField, $collectionItem])
            {
                $this->logCollectionRemove($ownerEntity, $ownerField, $collectionItem);
            }

            $this->scheduledCollectionRemovals = [];

            $this->requireFlush();
        }
    }

    public function logCollectionInsert(object $ownerEntity, string $ownerField, object $collectionItem): void
    {
        $log = $this->createAuditLog($ownerEntity, EntityModeEnum::CollectionInsert);

        $collectionInsert = $this->createAuditLogCollectionInsert($ownerEntity, $ownerField, $collectionItem);
        $collectionInsert->setAuditLog($log);

        $this->entityManager->persist($collectionInsert);
        $this->entityManager->persist($log);

        $this->handleAuditLogPropertyEvent(PropertyEvent::ON_COLLECTION_INSERT, $log, $ownerEntity);

        $this->requireFlush();
    }

    public function logCollectionRemove(object $ownerEntity, string $ownerField, object $collectionItem): void
    {
        $log = $this->createAuditLog($ownerEntity, EntityModeEnum::CollectionRemove);

        $collectionInsert = $this->createAuditLogCollectionRemove($ownerEntity, $ownerField, $collectionItem);
        $collectionInsert->setAuditLog($log);

        $this->entityManager->persist($collectionInsert);
        $this->entityManager->persist($log);

        $this->handleAuditLogPropertyEvent(PropertyEvent::ON_COLLECTION_REMOVE, $log, $ownerEntity);

        $this->requireFlush();
    }

    private function handleAuditLogPropertyEvent(PropertyEvent $event, AuditLogEntity $log, object $entity)
    {
        $eventArgs = new PropertyEventArgs($entity);
        $this->eventDispatcher->dispatch($eventArgs, $event->name);

        foreach ($eventArgs->getProperties() as $name => $value)
        {
            $property = $this->createAuditLogProperty($log, $name, $value);
            $this->entityManager->persist($property);
        }
    }

    private function createAuditLog(object $entity, EntityModeEnum $mode): AuditLogEntity
    {
        return $this->factory->createAuditLog()
            ->setEntity($this->getOrCreateAuditLogEntity($entity))
            ->setEntityId($this->getEntityId($entity))
            ->setMode($mode)
            ->setDate($this->dateTimeProvider->getNow());
    }

    private function createAuditLogProperty(AuditLogEntity $auditLog, string $name, string $value): AuditLogProperty
    {
        return $this->factory->createAuditLogProperty()
            ->setAuditLog($auditLog)
            ->setName($name)
            ->setValue($value);
    }

    private function createAuditLogEntity(object $entity): AuditLogEntityEntity
    {
        return $this->factory->createAuditLogEntity()
            ->setEntity($entity::class);
    }

    private function createAuditLogFieldCreate(string $fieldName, mixed $value): AuditLogFieldCreate
    {
        return $this->factory->createAuditLogFieldCreate()
            ->setFieldName($fieldName)
            ->setValue($this->createAuditLogFieldStringValue($value));
    }

    private function createAuditLogCollectionInsert(object $ownerEntity, string $ownerField, object $collectionItem): AuditLogCollectionInsert
    {
        return $this->factory->createAuditLogCollectionInsert()
            ->setFieldName($ownerField)
            ->setEntity($this->getOrCreateAuditLogEntity($ownerEntity))
            ->setEntityId($this->getEntityId($collectionItem));
    }

    private function createAuditLogCollectionRemove(object $ownerEntity, string $ownerField, object $collectionItem): AuditLogCollectionRemove
    {
        return $this->factory->createAuditLogCollectionRemove()
            ->setFieldName($ownerField)
            ->setEntity($this->getOrCreateAuditLogEntity($ownerEntity))
            ->setEntityId($this->getEntityId($collectionItem));
    }

    private function createAuditLogFieldStringValue(mixed $value): ?string
    {
        if ($value === null)
        {
            return null;
        }

        if (is_scalar($value))
        {
            return (string) $value;
        }

        if ($value instanceof DateTimeInterface)
        {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_object($value) && method_exists($value, '__toString'))
        {
            return (string) $value;
        }

        if (is_resource($value))
        {
            return '[resource]';
        }

        return $value::class;
    }

    private function createAuditLogFieldUpdate(string $fieldName, mixed $newValue, mixed $oldValue): AuditLogFieldUpdate
    {
        return $this->factory->createAuditLogFieldUpdate()
            ->setFieldName($fieldName)
            ->setNewValue($this->createAuditLogFieldStringValue($newValue))
            ->setOldValue($this->createAuditLogFieldStringValue($oldValue));
    }

    private function getEntityId(object $entity): int
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();

        return (int) $unitOfWork->getEntityIdentifier($entity)['id'];
    }

    private function getEntityFieldValues(object $entity): array
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $classMetadata = $this->entityManager->getClassMetadata($entity::class);
        $identifier = $unitOfWork->getEntityIdentifier($entity);

        if (!$unitOfWork->isInIdentityMap($entity))
        {
            $unitOfWork->registerManaged($entity, $identifier, []);
        }

        $values = [];

        foreach ($classMetadata->getFieldNames() as $fieldName)
        {
            $values[$fieldName] = $unitOfWork->getOriginalEntityData($entity)[$fieldName] ?? null;
        }

        return $values;
    }

    private function getOrCreateAuditLogEntity(object $entity): AuditLogEntityEntity
    {
        if (array_key_exists($entity::class, $this->auditLogEntityCache))
        {
            return $this->auditLogEntityCache[$entity::class];
        }

        $auditLogEntity = $this->auditLogEntityRepository->getByEntityClass($entity::class);

        if ($auditLogEntity === null)
        {
            $auditLogEntity = $this->createAuditLogEntity($entity);
            $this->auditLogEntityCache[$entity::class] = $auditLogEntity;
            $this->entityManager->persist($auditLogEntity);
        }

        return $auditLogEntity;
    }

    public function requireFlush(): void
    {
        $this->requiresFlush = true;
    }

    public function isFlushRequired(): bool
    {
        return $this->requiresFlush;
    }

    public function flush(): void
    {
        $this->requiresFlush = false;
        $this->entityManager->flush();
    }
}