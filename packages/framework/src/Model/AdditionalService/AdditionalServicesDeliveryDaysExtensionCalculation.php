<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Shopsys\FrameworkBundle\Model\Cart\Cart;

class AdditionalServicesDeliveryDaysExtensionCalculation
{
    public function calculateHighestDeliveryDaysExtension(Cart $cart): int
    {
        $highestDeliveryDaysExtension = 0;

        foreach ($cart->getItems() as $cartItem) {
            foreach ($cartItem->getAdditionalServices() as $additionalService) {
                $highestDeliveryDaysExtension = max(
                    $highestDeliveryDaysExtension,
                    $additionalService->getDeliveryDaysExtension(),
                );
            }
        }

        return $highestDeliveryDaysExtension;
    }
}
