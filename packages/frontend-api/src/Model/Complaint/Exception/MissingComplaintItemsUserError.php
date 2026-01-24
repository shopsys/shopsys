<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class MissingComplaintItemsUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'missing-complaint-items';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
