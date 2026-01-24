<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Flag\Exception;

use Override;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class FlagNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const string CODE = 'flag-not-found';

    #[Override]
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
