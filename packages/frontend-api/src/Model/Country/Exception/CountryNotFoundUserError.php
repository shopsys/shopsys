<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Country\Exception;

use Override;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class CountryNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const string CODE = 'country-not-found';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
