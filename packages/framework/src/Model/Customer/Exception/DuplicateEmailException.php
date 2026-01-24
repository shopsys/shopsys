<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class DuplicateEmailException extends Exception
{
    public function __construct(protected string $email, ?Exception $previous = null)
    {
        parent::__construct('User with email ' . $this->email . ' already exists.', 0, $previous);
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
