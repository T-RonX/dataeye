<?php

declare(strict_types=1);

namespace App\AuditLog;

use App\AuditLog\Entity\AuditLog as AuditLogEntity;
use App\AuditLog\Entity\AuditLogEntity as AuditLogEntityEntity;
use App\AuditLog\Entity\AuditLogFieldCreate;
use App\AuditLog\Entity\AuditLogFieldUpdate;
use App\AuditLog\Repository\AuditLogEntityRepository;
use App\DateTimeProvider\DateTimeProvider;
use Doctrine\ORM\EntityManagerInterface;

class AuditLog
{
    private bool $requiresFlush = false;
    private array $createCache = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AuditLogEntityRepository $auditLogEntityRepository,
        private readonly AuditLogFactory $factory,
        private readonly DateTimeProvider $dateTimeProvider,
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
            $field = $this->createAuditLogFieldCreate($fieldName, (string) $value);
            $field->setAuditLog($log);
            $this->entityManager->persist($field);
        }

        unset($this->createCache[spl_object_id($entity)]);
        $this->entityManager->persist($log);
        $this->requireFlush();
    }

    public function logUpdate(object $entity, array $fields): void
    {
        $log = $this->createAuditLog($entity, EntityModeEnum::Update);

        foreach ($fields as $fieldName => [$oldValue, $newValue])
        {
            $field = $this->createAuditLogFieldUpdate($fieldName, (string) $newValue, (string) $oldValue);
            $field->setAuditLog($log);
            $this->entityManager->persist($field);
        }

        $this->entityManager->persist($log);
        $this->requireFlush();
    }

    public function logRemove(object $entity): void
    {
        $log = $this->createAuditLog($entity, EntityModeEnum::Delete);

        $this->entityManager->persist($log);
        $this->requireFlush();
    }

    private function createAuditLog(object $entity, EntityModeEnum $mode): AuditLogEntity
    {
        return $this->factory->createAuditLog()
            ->setEntity($this->getOrCreateAuditLogEntity($entity))
            ->setEntityId($this->getEntityId($entity))
            ->setMode($mode)
            ->setDate($this->dateTimeProvider->getNow());
    }

    private function createAuditLogEntity(object $entity): AuditLogEntityEntity
    {
        return $this->factory->createAuditLogEntity()
            ->setEntity($entity::class);
    }

    private function createAuditLogFieldCreate(string $fieldName, string $value): AuditLogFieldCreate
    {
        return $this->factory->createAuditLogFieldCreate()
            ->setFieldName($fieldName)
            ->setValue($value);
    }

    private function createAuditLogFieldUpdate(string $fieldName, string $newValue, string $oldValue): AuditLogFieldUpdate
    {
        return $this->factory->createAuditLogFieldUpdate()
            ->setFieldName($fieldName)
            ->setNewValue($newValue)
            ->setOldValue($oldValue);
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
        $auditLogEntity = $this->auditLogEntityRepository->findOneBy(['entity' => $entity::class]);

        if ($auditLogEntity === null)
        {
            $auditLogEntity = $this->createAuditLogEntity($entity);
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