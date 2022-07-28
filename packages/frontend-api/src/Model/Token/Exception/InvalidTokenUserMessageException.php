<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token\Exception;

use Throwable;

class InvalidTokenUserMessageException extends TokenUserMessageException
{
    /**
     * @param string $message
     * @param array $messageData
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = 'Token is not valid.', array $messageData = [], int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $messageData, $code, $previous);
    }
}
