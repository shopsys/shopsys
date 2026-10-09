<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

class ResolvedChangesFormatter extends AbstractChangeSetFormatter
{
    public function __construct(
        protected readonly CollectionChangesFormatter $collectionChangesFormatter,
        protected readonly DataTypeFormatterRegistry $dataTypeFormatterRegistry,
    ) {
    }

    public function formatResolvedChanges(array $changeSet): string
    {
        $formattedChanges = [];

        foreach ($changeSet as $attribute => $changes) {
            $formattedChanges[] = match ($changes['dataType']) {
                'Collection' => t('Collection %collectionAttribute% was changed:<br> %changes%', [
                    '%collectionAttribute%' => $this->formatCode($attribute),
                    '%changes%' => $this->collectionChangesFormatter->formatChanges($changes),
                ]),
                default => t('Attribute %attribute% was changed %changes%', [
                    '%attribute%' => $this->formatCode($attribute),
                    '%changes%' => $this->formatAttributeChanges($changes),
                ]),
            };
        }

        // a block per change, a line break after a nested list would render an empty line
        return implode('', array_map(static fn (string $formattedChange) => sprintf('<div>%s</div>', $formattedChange), $formattedChanges));
    }

    /**
     * @param array{dataType: string, oldReadableValue: mixed, newReadableValue: mixed, oldValue: mixed, newValue: mixed} $changes
     */
    protected function formatAttributeChanges(array $changes): string
    {
        $dataTypeFormatter = $this->dataTypeFormatterRegistry->getDataTypeFormatter($changes['dataType']);

        return $this->formatFromToChanges(
            $dataTypeFormatter->formatValue($changes['oldReadableValue'], $changes['oldValue']),
            $dataTypeFormatter->formatValue($changes['newReadableValue'], $changes['newValue']),
        );
    }
}
