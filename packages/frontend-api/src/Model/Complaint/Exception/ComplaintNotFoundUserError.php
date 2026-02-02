<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint\Exception;

use Override;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ComplaintNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const string CODE = 'complaint-not-found';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
