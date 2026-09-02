<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ProductReviewStatusEnum extends AbstractEnum
{
    public const string STATUS_PENDING = 'pending';
    public const string STATUS_APPROVED = 'approved';
    public const string STATUS_REJECTED = 'rejected';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Pending') => static::STATUS_PENDING,
            t('Approved') => static::STATUS_APPROVED,
            t('Rejected') => static::STATUS_REJECTED,
        ];
    }
}
