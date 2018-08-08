<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class UserNotFoundByEmailAndDomainException extends UserNotFoundException
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var int
     */
    private $domainId;

    public function __construct(string $email, int $domainId, Exception $previous = null)
    {
        parent::__construct('User with email "' . $email . '" on domain "' . $domainId . '" not found.', $previous);

        $this->email = $email;
        $this->domainId = $domainId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
