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

    /**
     * @param string $productsPriceWithVat
     * @param int $domainId
     */
    public function isFree($productsPriceWithVat, $domainId): bool
    {
        $freeTransportAndPaymentPriceLimit = $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId);
        if ($freeTransportAndPaymentPriceLimit === null) {
            return false;
        }

        return $productsPriceWithVat >= $freeTransportAndPaymentPriceLimit;
    }

    /**
     * @param string $productsPriceWithVat
     * @param int $domainId
     */
    public function getRemainingPriceWithVat($productsPriceWithVat, $domainId): int
    {
        if (!$this->isFree($productsPriceWithVat, $domainId)) {
            return $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId) - $productsPriceWithVat;
        }

        return 0;
    }

    /**
     * @param int $domainId
     */
    protected function getFreeTransportAndPaymentPriceLimitOnDomain($domainId): string
    {
        return $this->pricingSetting->getFreeTransportAndPaymentPriceLimit($domainId);
    }
}
