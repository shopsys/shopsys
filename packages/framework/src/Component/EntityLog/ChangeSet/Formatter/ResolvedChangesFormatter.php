<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

class ResolvedChangesFormatter
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\CollectionChangesFormatter $collectionChangesFormatter
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ScalarDataTypeFormatter $scalarDataTypeFormatter
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\MoneyDataTypeFormatter $moneyDataTypeFormatter
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\DateTimeDataTypeFormatter $dateTimeDataTypeFormatter
     */
    public function __construct(
        protected readonly CollectionChangesFormatter $collectionChangesFormatter,
        protected readonly ScalarDataTypeFormatter $scalarDataTypeFormatter,
        protected readonly MoneyDataTypeFormatter $moneyDataTypeFormatter,
        protected readonly DateTimeDataTypeFormatter $dateTimeDataTypeFormatter,
    ) {
    }

    /**
     * @param array $changeSet
     * @return string
     */
    public function formatResolvedChanges(array $changeSet): string
    {
        $formattedChanges = [];

        foreach ($changeSet as $attribute => $changes) {
            if ($changes['dataType'] === 'Collection') {
                $formattedChanges[] = t(
                    'Collection %collectionAttribute% was changed:<br> %changes%',
                    [
                        '%collectionAttribute%' => $attribute,
                        '%changes%' => $this->collectionChangesFormatter->formatChanges($changes),
                    ],
                );

                continue;
            }

            if (in_array($changes['dataType'], ['DateTimeImmutable', 'DateTime'], true)) {
                $formattedChanges[] = t(
                    'Attribute %attribute% was changed %changes%',
                    [
                        '%attribute%' => $attribute,
                        '%changes%' => $this->dateTimeDataTypeFormatter->formatChanges($changes),
                    ],
                );

                continue;
            }

            if ($changes['dataType'] === 'Money') {
                $formattedChanges[] = t(
                    'Attribute %attribute% was changed %changes%',
                    [
                        '%attribute%' => $attribute,
                        '%changes%' => $this->moneyDataTypeFormatter->formatChanges($changes),
                    ],
                );

                continue;
            }

            $formattedChanges[] = t(
                'Attribute %attribute% was changed %changes%',
                [
                    '%attribute%' => $attribute,
                    '%changes%' => $this->scalarDataTypeFormatter->formatChanges($changes),
                ],
            );
        }

        return implode('<br>', $formattedChanges);
    }
}
