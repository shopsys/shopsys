<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Security\LoginAdministratorAsUserUrlProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomerExtensionTwig extends AbstractExtension
{
    public function __construct(protected readonly LoginAdministratorAsUserUrlProvider $loginAsCustomerUserUrlProvider)
    {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions()
    {
        return [
            new TwigFunction('getLoginAsUserUrl', $this->getLoginAsUserUrl(...)),
        ];
    }

    protected function getLoginAsUserUrl(CustomerUser $customerUser): ?string
    {
        return $this->loginAsCustomerUserUrlProvider->getLoginAsCustomerUserUrl($customerUser);
    }
}
