<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security;

interface ResetPasswordInterface
{
    /**
     * @return int
     */
    public function getId();

    public function isResetPasswordHashValid(?string $hash): bool;

    /**
     * @return string
     */
    public function getResetPasswordHash();

    /**
     * @return string
     */
    public function getEmail();
}
