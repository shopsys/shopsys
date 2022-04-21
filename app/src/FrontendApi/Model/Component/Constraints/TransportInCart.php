<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class TransportInCart extends Constraint
{
    public const UNAVAILABLE_TRANSPORT_ERROR = '2414f8de-52fd-4a54-ab07-6f6c9f68e5c9';
    public const UNAVAILABLE_PICKUP_PLACE_ERROR = '057c0f78-2ae9-453b-8f4c-78d7044bea11';
    public const WEIGHT_LIMIT_EXCEEDED_ERROR = 'f53edb6a-f227-473c-b89e-5b17bfd8b787';

    public string $unavailableTransportMessage = 'Transport with provided UUID is not available';

    public string $unavailablePickupPlaceMessage = 'Pickup place with provided UUID is not available';

    public string $weightLimitExceededMessage = 'Selected transport weight limit has been exceeded';

    /**
     * @var array<string, string>
     */
    protected static $errorNames = [
        self::UNAVAILABLE_TRANSPORT_ERROR => 'UNAVAILABLE_TRANSPORT_ERROR',
        self::UNAVAILABLE_PICKUP_PLACE_ERROR => 'UNAVAILABLE_PICKUP_PLACE_ERROR',
        self::WEIGHT_LIMIT_EXCEEDED_ERROR => 'WEIGHT_LIMIT_EXCEEDED_ERROR',
    ];

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
