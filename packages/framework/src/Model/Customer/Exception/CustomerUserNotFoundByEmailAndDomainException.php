<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class CustomerUserNotFoundByEmailAndDomainException extends CustomerUserNotFoundException
{
    protected string $email;

    protected int $domainId;

    /**
     * @param string $email
     * @param int $domainId
     * @param \Exception|null $previous
     */
    public function __construct(string $email, int $domainId, ?Exception $previous = null)
    {
        parent::__construct('User with email "' . $email . '" on domain "' . $domainId . '" not found.', $previous);

        $this->email = $email;
        $this->domainId = $domainId;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
