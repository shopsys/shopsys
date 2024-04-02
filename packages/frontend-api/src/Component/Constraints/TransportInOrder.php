<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class TransportInOrder extends Constraint
{
    public const TRANSPORT_NOT_SET_ERROR = '2a993918-fb80-4aba-a94c-dcb165dc2817';
    public const TRANSPORT_UNAVAILABLE_ERROR = '74fe6c4d-928a-4459-b7c1-01c34043de69';
    public const CHANGED_TRANSPORT_PRICE_ERROR = '89af9f05-75de-4d07-8033-34ab63c75920';
    public const PICKUP_PLACE_UNAVAILABLE_ERROR = 'd86e0f9c-747d-4438-b3e2-991f4e963f41';
    public const WEIGHT_LIMIT_EXCEEDED_ERROR = 'b1eb2af1-2e7a-4463-aa5e-fb2bf82a30ef';
    public const MISSING_PICKUP_PLACE_IDENTIFIER_ERROR = '72cfdb60-9779-4903-a845-57e14b730795';

    public string $transportNotSetMessage = 'Transport must be set in cart before sending the order';

    public string $transportUnavailableMessage = 'Selected transport is not available';

    public string $changedTransportPriceMessage = 'Selected transport price has changed';

    public string $pickupPlaceUnavailableMessage = 'Selected pickup place is not available';

    public string $weightLimitExceeded = 'Selected transport weight limit has been exceeded';

    public string $missingPickupPlaceIdentifierMessage = 'Selected transport needs to have pickup place identifier set';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::TRANSPORT_NOT_SET_ERROR => 'TRANSPORT_NOT_SET_ERROR',
        self::TRANSPORT_UNAVAILABLE_ERROR => 'TRANSPORT_UNAVAILABLE_ERROR',
        self::CHANGED_TRANSPORT_PRICE_ERROR => 'CHANGED_TRANSPORT_PRICE_ERROR',
        self::PICKUP_PLACE_UNAVAILABLE_ERROR => 'PICKUP_PLACE_UNAVAILABLE_ERROR',
        self::WEIGHT_LIMIT_EXCEEDED_ERROR => 'WEIGHT_LIMIT_EXCEEDED_ERROR',
        self::MISSING_PICKUP_PLACE_IDENTIFIER_ERROR => 'MISSING_PICKUP_PLACE_IDENTIFIER_ERROR',
    ];

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
