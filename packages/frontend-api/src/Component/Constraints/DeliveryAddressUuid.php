<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class DeliveryAddressUuid extends Constraint
{
    public const LOGIN_REQUIRED_ERROR = '9dcda0d3-7264-4c5f-9b35-f5b155f997f9';

    public string $loginRequiredErrorMessage = 'You must be logged in if you want to provide the delivery address UUID in the order input';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::LOGIN_REQUIRED_ERROR => 'LOGIN_REQUIRED_ERROR',
    ];

    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
