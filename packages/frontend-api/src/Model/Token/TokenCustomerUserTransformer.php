<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class TokenCustomerUserTransformer
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $user
     * @return array
     */
    public static function transform(CustomerUser $user): array
    {
        return [
            'uuid' => $user->getUuid(),
            'email' => $user->getEmail(),
            'domainId' => $user->getDomainId(),
            'roles' => $user->getRoles(),
        ];
    }
}
