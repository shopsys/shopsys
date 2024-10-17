<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Login;

use Convertim\Customer\LoginDetail;
use Shopsys\ConvertimBundle\Model\Customer\Exception\LoginDetailsNotFoundException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade as FrameworkCustomerUserFacade;

class LoginDetailFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly FrameworkCustomerUserFacade $customerUserFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param string $email
     * @return \Convertim\Customer\LoginDetail
     */
    public function createLoginDetail(string $email): LoginDetail
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $this->domain->getId());

        if ($customerUser === null) {
            throw new LoginDetailsNotFoundException($email);
        }

        if ($customerUser->getTelephone() !== null) {
            return new LoginDetail(
                $customerUser->getUuid(),
                $customerUser->getTelephone(),
            );
        }

        foreach ($customerUser->getCustomer()->getDeliveryAddresses() as $deliveryAddress) {
            if ($deliveryAddress->getTelephone() !== null) {
                return new LoginDetail(
                    $customerUser->getUuid(),
                    $deliveryAddress->getTelephone(),
                );
            }
        }

        throw new LoginDetailsNotFoundException($email);
    }
}
