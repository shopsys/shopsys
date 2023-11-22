<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class DuplicateEmailException extends Exception implements CustomerUserException
{
    protected string $email;

    /**
     * @param string $email
     * @param \Exception|null $previous
     */
    public function __construct(string $email, $previous = null)
    {
        $this->email = $email;

        parent::__construct('User with email ' . $this->email . ' already exists.', 0, $previous);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
