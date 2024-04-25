<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class DeliveryAddressUuid extends Constraint
{
    public const LOGIN_REQUIRED_ERROR = '9dcda0d3-7264-4c5f-9b35-f5b155f997f9';

    public string $loginRequiredErrorMessage = 'You must be logged in if you want to provide the delivery address UUID in the order input';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::LOGIN_REQUIRED_ERROR => 'LOGIN_REQUIRED_ERROR',
    ];

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
