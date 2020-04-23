<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation as BaseTransportPriceCalculation;

/**
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price calculatePrice(\App\Model\Transport\Transport $transport, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price calculateIndependentPrice(\App\Model\Transport\Transport $transport, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] getCalculatedPricesIndexedByTransportId(\App\Model\Transport\Transport[] $transports, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice, int $domainId)
 */
class TransportPriceCalculation extends BaseTransportPriceCalculation
{
    /**
     * @param \App\Model\Transport\Transport[] $transports
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     * @param bool $transportForFree
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function getCalculatedPricesIndexedByTransportIdByFreeTransport(
        array $transports,
        Currency $currency,
        int $domainId,
        bool $transportForFree
    ): array {
        $transportsPricesByTransportId = [];
        foreach ($transports as $transport) {
            if ($transportForFree) {
                $transportsPricesByTransportId[$transport->getId()] = Price::zero();
                continue;
            }

            $transportsPricesByTransportId[$transport->getId()] = $this->calculatePrice(
                $transport,
                $currency,
                Price::zero(),
                $domainId
            );
        }

        return $transportsPricesByTransportId;
    }
}
