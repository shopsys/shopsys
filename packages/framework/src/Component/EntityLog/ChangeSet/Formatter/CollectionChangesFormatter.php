<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

class CollectionChangesFormatter
{
    /**
     * @param array{insertedItems: array<\Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges>, deletedItems: array<\Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges>} $changes
     * @return string
     */
    public function formatChanges(array $changes): string
    {
        $formattedCollectionChanges = [];
        $this->formatInsertedItems($changes['insertedItems'], $formattedCollectionChanges);
        $this->formatDeletedItems($changes['deletedItems'], $formattedCollectionChanges);

        return implode(',<br> ', $formattedCollectionChanges);
    }

    /**
     * @param array $insertedChanges
     * @param string[] $formattedCollectionChanges
     */
    protected function formatInsertedItems(array $insertedChanges, array &$formattedCollectionChanges): void
    {
        foreach ($insertedChanges as $insertedChange) {
            $formattedCollectionChanges[] = t('Created %dataType%: "%readableValue%"', ['%dataType%' => $insertedChange['dataType'], '%readableValue%' => $insertedChange['newReadableValue']]);
        }
    }

    /**
     * @param array $deletedChanges
     * @param string[] $formattedCollectionChanges
     */
    protected function formatDeletedItems(array $deletedChanges, array &$formattedCollectionChanges): void
    {
        foreach ($deletedChanges as $deletedChange) {
            $formattedCollectionChanges[] = t('Removed %dataType%: "%readableValue%"', ['%dataType%' => $deletedChange['dataType'], '%readableValue%' => $deletedChange['oldReadableValue']]);
        }
    }
}
