<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Error;

use Overblog\GraphQLBundle\Error\UserError;

abstract class UserEntityNotFoundError extends UserError
{
    /**
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        parent::__construct($message, 404);
    }
}
