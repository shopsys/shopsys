<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class AdministratorMcpTokenHasher
{
    public function __construct(
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
    }

    public function hash(string $secret): string
    {
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(Administrator::class);

        return $passwordHasher->hash($secret);
    }

    public function verify(string $hashedSecret, string $secret): bool
    {
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(Administrator::class);

        return $passwordHasher->verify($hashedSecret, $secret);
    }
}
