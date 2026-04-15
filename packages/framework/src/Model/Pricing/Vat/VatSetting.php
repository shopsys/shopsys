<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class VatSetting
{
    protected const string DEFAULT_VAT = 'defaultVatId';

    public function __construct(
        protected readonly Setting $setting,
    ) {
    }

    public function getDefaultVatId(int $domainId): int
    {
        return (int)$this->setting->getForDomain(
            static::DEFAULT_VAT,
            $domainId,
        );
    }

    public function setDefaultVatId(int $vatId, int $domainId): void
    {
        $this->setting->setForDomain(
            static::DEFAULT_VAT,
            $vatId,
            $domainId,
        );
    }
}
