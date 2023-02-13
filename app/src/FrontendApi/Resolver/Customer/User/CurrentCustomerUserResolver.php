<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrontendApiBundle\Model\Resolver\Customer\User\CurrentCustomerUserResolver as BaseCurrentCustomerUserResolver;

class CurrentCustomerUserResolver extends BaseCurrentCustomerUserResolver
{
    /**
     * @return \App\Model\Customer\User\CustomerUser|null
     */
    public function resolveNullableCurrentCustomerUser(): ?CustomerUser
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        return $customerUser;
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolveNullableCurrentCustomerUser' => 'resolveNullableCurrentCustomerUser',
        ];
    }
}
