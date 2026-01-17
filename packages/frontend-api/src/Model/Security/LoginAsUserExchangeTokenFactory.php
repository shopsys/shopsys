<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class LoginAsUserExchangeTokenFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(
        string $hashedToken,
        CustomerUser $customerUser,
        Administrator $administrator,
        DateTimeImmutable $expiresAt,
    ): LoginAsUserExchangeToken {
        $entityClassName = $this->entityNameResolver->resolve(LoginAsUserExchangeToken::class);

        return new $entityClassName($hashedToken, $customerUser, $administrator, $expiresAt);
    }
}
