<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;

class AdditionalServicesDeliveryDaysExtensionCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     */
    public function calculateHighestDeliveryDaysExtension(array $quantifiedProducts): int
    {
        $highestDeliveryDaysExtension = 0;

        foreach ($quantifiedProducts as $quantifiedProduct) {
            $additionalServices = $quantifiedProduct->getAdditionalData(QuantifiedProduct::ADDITIONAL_SERVICES_KEY) ?? [];

            foreach ($additionalServices as $additionalService) {
                $highestDeliveryDaysExtension = max(
                    $highestDeliveryDaysExtension,
                    $additionalService->getDeliveryDaysExtension(),
                );
            }
        }

        return $highestDeliveryDaysExtension;
    }
}
