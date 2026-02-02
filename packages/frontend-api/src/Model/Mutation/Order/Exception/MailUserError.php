<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Order\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class MailUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'mail-failed';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
