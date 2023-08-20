<?php

declare(strict_types=1);

namespace App\Collection;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

readonly class DoctrineCollectionUpdater
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param object[] $entities
     */
    public function update(Collection $collection, array $entities, ?callable $targetEntityCallback = null): CollectionUpdate
    {
        $targetEntityCallback = $targetEntityCallback ?: static fn(object $entity): object => $entity;

        $itemsIdentityMap = $this->createItemsIdentityMap($collection, $targetEntityCallback);
        $entitiesIdentityMap = $this->createEntitiesIdentityMap($entities);

        $entitiesToAdd = array_diff_key($entitiesIdentityMap, $itemsIdentityMap);
        $entitiesToRemove = array_diff_key($itemsIdentityMap, $entitiesIdentityMap);

        $allRemovedItems = $this->removeItemsFromCollection($collection, $entitiesToRemove, $targetEntityCallback);

        return new CollectionUpdate($entitiesToAdd, $allRemovedItems);
    }

    private function createItemsIdentityMap(Collection $collection, callable $targetEntityCallback): array
    {
        $map = [];

        foreach ($collection as $item)
        {
            $entity = $targetEntityCallback($item);
            $map[$this->getEntityIdentity($entity)] = $entity;
        }

        return $map;
    }

    /**
     * @param object[] $entities
     */
    private function createEntitiesIdentityMap(array $entities): array
    {
        $map = [];

        foreach ($entities as $entity)
        {
            $map[$this->getEntityIdentity($entity)] = $entity;
        }

        return $map;
    }

    private function getEntityIdentity(object $entity): string
    {
        return implode('|', $this->entityManager->getUnitOfWork()->getEntityIdentifier($entity));
    }

    /**
     * @param object[] $entities
     *
     * @return object[]
     */
    private function removeItemsFromCollection(Collection $collection, array $entities, ?callable $fetchTargetEntityCallback): array
    {
        $removedItems = [];

        foreach ($entities as $entity)
        {
            $removedItems = [...$removedItems, ...$this->removeItemFromCollection($collection, $entity, $fetchTargetEntityCallback)];
        }

        return $removedItems;
    }

    /**
     * @return object[]
     */
    private function removeItemFromCollection(Collection $collection, object $entity, ?callable $targetEntityCallback): array
    {
        $removedItems = [];

        $collectionItems = $collection->filter(static function (object $collectionItem) use ($entity, $targetEntityCallback): bool {
            return spl_object_id($targetEntityCallback($collectionItem)) === spl_object_id($entity);
        })->toArray();

        foreach ($collectionItems as $collectionItem)
        {
            if ($collectionItem !== null)
            {
                $collection->removeElement($collectionItem);
                //$this->entityManager->remove($collectionItem);
            }

            $removedItems[] = $collectionItem;
        }

        return $removedItems;
    }
}