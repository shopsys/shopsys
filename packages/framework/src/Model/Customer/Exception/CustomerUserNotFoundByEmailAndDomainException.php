<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class CustomerUserNotFoundByEmailAndDomainException extends CustomerUserNotFoundException
{
    public function __construct(protected string $email, protected int $domainId, ?Exception $previous = null)
    {
        parent::__construct('User with email "' . $email . '" on domain "' . $domainId . '" not found.', $previous);
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
