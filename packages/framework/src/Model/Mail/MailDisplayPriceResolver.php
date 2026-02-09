<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class MailDisplayPriceResolver
{
    public const string DISPLAY_PRICE_WITH_VAT = 'with_vat';
    public const string DISPLAY_PRICE_WITHOUT_VAT = 'without_vat';
    public const string DISPLAY_PRICE_BOTH = 'both';
    protected const string DISPLAY_PRICE_SELLING = 'selling_price';

    public function __construct(
        protected readonly string $mailTemplateDisplayPrice,
        protected readonly PricingSetting $pricingSetting,
    ) {
    }

    public function getDisplayPrice(): string
    {
        if ($this->mailTemplateDisplayPrice === static::DISPLAY_PRICE_SELLING) {
            return $this->pricingSetting->getSellingPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT
                ? static::DISPLAY_PRICE_WITH_VAT
                : static::DISPLAY_PRICE_WITHOUT_VAT;
        }

        return static::DISPLAY_PRICE_BOTH;
    }
}
