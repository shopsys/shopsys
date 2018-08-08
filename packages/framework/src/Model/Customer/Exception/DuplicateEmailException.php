<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class DuplicateEmailException extends Exception implements CustomerException
{
    /**
     * @var string
     */
    private $email;

    public function __construct(string $email, ?\Exception $previous = null)
    {
        $this->email = $email;

        parent::__construct('User with email ' . $this->email . ' already exists.', 0, $previous);
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
