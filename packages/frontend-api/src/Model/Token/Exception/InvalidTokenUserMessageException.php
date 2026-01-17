<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token\Exception;

use Throwable;

class InvalidTokenUserMessageException extends TokenUserMessageException
{
    public function __construct(
        string $message = 'Token is not valid.',
        array $messageData = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $messageData, $code, $previous);
    }
}
