<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Security\LoginAdministratorAsUserUrlProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomerExtensionTwig extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Security\LoginAdministratorAsUserUrlProvider $loginAsCustomerUserUrlProvider
     */
    public function __construct(protected readonly LoginAdministratorAsUserUrlProvider $loginAsCustomerUserUrlProvider)
    {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getSsoLoginAsUserUrl', $this->getSsoLoginAsUserUrl(...)),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string
     */
    protected function getSsoLoginAsUserUrl(CustomerUser $customerUser): string
    {
        return $this->loginAsCustomerUserUrlProvider->getSsoLoginAsCustomerUserUrl($customerUser);
    }
}
