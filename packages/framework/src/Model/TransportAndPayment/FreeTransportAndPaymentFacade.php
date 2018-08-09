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

    /**
     * @return bool
     */
    public function isActive($domainId)
    {
        return $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId) !== null;
    }

    /**
     * @param string $productsPriceWithVat
     * @param int $domainId
     * @return bool
     */
    public function isFree($productsPriceWithVat, $domainId)
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
     * @return int
     */
    public function getRemainingPriceWithVat($productsPriceWithVat, $domainId)
    {
        if (!$this->isFree($productsPriceWithVat, $domainId)) {
            return $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId) - $productsPriceWithVat;
        }

        return 0;
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getFreeTransportAndPaymentPriceLimitOnDomain($domainId)
    {
        return $this->pricingSetting->getFreeTransportAndPaymentPriceLimit($domainId);
    }
}
