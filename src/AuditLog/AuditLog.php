<?php

declare(strict_types=1);

namespace App\AuditLog;

use App\AuditLog\Entity\AuditLog as AuditLogEntity;
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