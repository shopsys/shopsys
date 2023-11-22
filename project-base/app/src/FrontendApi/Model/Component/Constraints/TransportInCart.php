<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class TransportInCart extends Constraint
{
    public const UNAVAILABLE_TRANSPORT_ERROR = '2414f8de-52fd-4a54-ab07-6f6c9f68e5c9';
    public const UNAVAILABLE_PICKUP_PLACE_ERROR = '057c0f78-2ae9-453b-8f4c-78d7044bea11';
    public const WEIGHT_LIMIT_EXCEEDED_ERROR = 'f53edb6a-f227-473c-b89e-5b17bfd8b787';
    public const MISSING_PICKUP_PLACE_IDENTIFIER_ERROR = '7c12df56-2fb7-4782-b8d7-5755cf53fd3a';
    public const INVALID_TRANSPORT_PAYMENT_COMBINATION_ERROR = 'd96b9e7d-f532-4249-8d50-c77e3a67a4cf';

    public string $unavailableTransportMessage = 'Transport with provided UUID is not available';

    public string $unavailablePickupPlaceMessage = 'Pickup place with provided UUID is not available';

    public string $weightLimitExceededMessage = 'Selected transport weight limit has been exceeded';

    public string $missingPickupPlaceIdentifierMessage = 'Selected transport needs to have pickup place identifier set';

    public string $invalidTransportPaymentCombinationMessage = 'The transport is not allowed in combination with already selected payment';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::UNAVAILABLE_TRANSPORT_ERROR => 'UNAVAILABLE_TRANSPORT_ERROR',
        self::UNAVAILABLE_PICKUP_PLACE_ERROR => 'UNAVAILABLE_PICKUP_PLACE_ERROR',
        self::WEIGHT_LIMIT_EXCEEDED_ERROR => 'WEIGHT_LIMIT_EXCEEDED_ERROR',
        self::MISSING_PICKUP_PLACE_IDENTIFIER_ERROR => 'MISSING_PICKUP_PLACE_IDENTIFIER_ERROR',
        self::INVALID_TRANSPORT_PAYMENT_COMBINATION_ERROR => 'INVALID_TRANSPORT_PAYMENT_COMBINATION_ERROR',
    ];

    /**
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
