<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use Override;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Security\LoginAdministratorAsUserUrlProvider as BaseLoginAdministratorAsUserUrlProvider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class LoginAdministratorAsUserUrlProvider extends BaseLoginAdministratorAsUserUrlProvider
{
    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector
     */
    public function __construct(
        protected readonly RouterInterface $router,
        protected RouteCsrfProtector $routeCsrfProtector,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string
     */
    #[Override]
    public function getLoginAsCustomerUserUrl(CustomerUser $customerUser): string
    {
        $routeName = 'admin_customeruser_loginascustomeruser';

        $parameters = [
            RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $this->routeCsrfProtector->getCsrfTokenByRoute($routeName),
            'customerUserId' => $customerUser->getId(),
        ];

        return $this->router->generate(
            $routeName,
            $parameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
