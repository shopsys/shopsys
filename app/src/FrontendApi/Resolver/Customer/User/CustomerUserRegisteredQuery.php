<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Customer\User;

use App\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

final class CustomerUserRegisteredQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly CustomerUserFacade $customerUserFacade,
        private readonly Domain $domain
    ) {
    }

    /**
     * @param string $email
     * @return bool
     */
    public function isCustomerUserRegisteredQuery(string $email): bool
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $this->domain->getId());

        return $customerUser !== null;
    }
}
