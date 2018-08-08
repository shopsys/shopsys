<?php

namespace Shopsys\FrameworkBundle\Model\TransportAndPayment;

use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class FreeTransportAndPaymentFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    protected $pricingSetting;

    public function __construct(PricingSetting $pricingSetting)
    {
        $this->pricingSetting = $pricingSetting;
    }

    public function isActive($domainId): bool
    {
        return $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId) !== null;
    }

    public function isFree(string $productsPriceWithVat, int $domainId): bool
    {
        $freeTransportAndPaymentPriceLimit = $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId);
        if ($freeTransportAndPaymentPriceLimit === null) {
            return false;
        }

        return $productsPriceWithVat >= $freeTransportAndPaymentPriceLimit;
    }

    public function getRemainingPriceWithVat(string $productsPriceWithVat, int $domainId): int
    {
        if (!$this->isFree($productsPriceWithVat, $domainId)) {
            return $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId) - $productsPriceWithVat;
        }

        return 0;
    }

    protected function getFreeTransportAndPaymentPriceLimitOnDomain(int $domainId): string
    {
        return $this->pricingSetting->getFreeTransportAndPaymentPriceLimit($domainId);
    }
}
