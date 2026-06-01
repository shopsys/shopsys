<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;

class TokenCustomerUserTransformer
{
    /**
     * @return array{uuid: string, email: string, fullName: string, roles: string[], pricingGroupId: int}
     */
    public function transform(CustomerUser $user): array
    {
        return [
            FrontendApiUser::CLAIM_UUID => $user->getUuid(),
            FrontendApiUser::CLAIM_EMAIL => $user->getEmail(),
            FrontendApiUser::CLAIM_FULL_NAME => $user->getFullName(),
            FrontendApiUser::CLAIM_ROLES => $user->getRoles(),
            FrontendApiUser::CLAIM_PRICING_GROUP_ID => $user->getPricingGroup()->getId(),
        ];
    }
}
