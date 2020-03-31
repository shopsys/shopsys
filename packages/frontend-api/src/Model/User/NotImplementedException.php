<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\User;

class NotImplementedException extends \RuntimeException
{
    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
