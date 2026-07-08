<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class TransportUnavailabilityReasonInCartEnum extends AbstractEnum
{
    public const string PERSONAL_PICKUP_REQUIRED = 'personal_pickup_required';
    public const string EXCLUDED_FOR_PRODUCT = 'excluded_for_product';
}
