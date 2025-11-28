<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class LoginAsUserExchangeTokenFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param string $hashedToken
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param \DateTimeImmutable $expiresAt
     * @return \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeToken
     */
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
