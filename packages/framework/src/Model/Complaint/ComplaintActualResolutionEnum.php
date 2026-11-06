<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class ComplaintActualResolutionEnum extends ComplaintResolutionEnum
{
    public const string REJECTED = 'rejected';

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getAllIndexedByTranslations(): array
    {
        return [
            ...parent::getAllIndexedByTranslations(),
            t('Rejected') => static::REJECTED,
        ];
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getAllIndexedByTranslationsForCustomer(): array
    {
        return [
            ...parent::getAllIndexedByTranslationsForCustomer(),
            t('Rejected', domain: Translator::CUSTOMER_TRANSLATION_DOMAIN) => static::REJECTED,
        ];
    }

    /**
     * @return string[]
     */
    public function getResolutionsKeepingClaimedQuantity(): array
    {
        return [static::MONEY_RETURN];
    }
}
