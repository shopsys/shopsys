<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use LogicException;
use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ComplaintStatusTypeEnum extends AbstractEnum
{
    public const string STATUS_TYPE_NEW = 'new';
    public const string STATUS_TYPE_RESOLVED = 'resolved';
    public const string STATUS_TYPE_RESOLVED_CLAIMABLE_AGAIN = 'resolved_claimable_again';
    public const string STATUS_TYPE_IN_PROGRESS = 'in_progress';

    public function getTranslation(string $statusType): string
    {
        $this->validateCase($statusType);

        return array_flip($this->getAllIndexedByTranslations())[$statusType];
    }

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        $translations = [
            t('New [adjective]') => static::STATUS_TYPE_NEW,
            t('In Progress') => static::STATUS_TYPE_IN_PROGRESS,
            t('Resolved, the products cannot be complained about again') => static::STATUS_TYPE_RESOLVED,
            t('Resolved, the products can be complained about again') => static::STATUS_TYPE_RESOLVED_CLAIMABLE_AGAIN,
        ];

        $statusTypesWithoutTranslation = array_diff($this->getAllCases(), $translations);

        if ($statusTypesWithoutTranslation !== []) {
            throw new LogicException(
                'Missing translation of complaint status types: ' . implode(', ', $statusTypesWithoutTranslation),
            );
        }

        return $translations;
    }

    /**
     * @return array<string, string>
     */
    public function getAssignableIndexedByTranslations(): array
    {
        return array_filter(
            $this->getAllIndexedByTranslations(),
            static fn (string $statusType): bool => $statusType !== static::STATUS_TYPE_NEW,
        );
    }

    /**
     * @return string[]
     */
    public function getStatusTypesReleasingClaimedQuantity(): array
    {
        return [static::STATUS_TYPE_RESOLVED_CLAIMABLE_AGAIN];
    }
}
