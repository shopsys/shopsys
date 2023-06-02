<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Order\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class OrderEmailsNotSentUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    private const CODE = 'order-emails-not-sent';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
