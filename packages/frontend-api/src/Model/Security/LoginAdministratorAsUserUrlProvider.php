<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Security\LoginAdministratorAsUserUrlProvider as BaseLoginAdministratorAsUserUrlProvider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class LoginAdministratorAsUserUrlProvider extends BaseLoginAdministratorAsUserUrlProvider
{
    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(
        protected readonly RouterInterface $router,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string
     */
    #[Override]
    public function getLoginAsCustomerUserUrl(CustomerUser $customerUser): string
    {
        return $this->router->generate(
            'admin_customeruser_loginascustomeruser',
            [
                'customerUserId' => $customerUser->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
