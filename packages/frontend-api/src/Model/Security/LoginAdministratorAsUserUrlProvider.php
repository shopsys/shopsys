<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Controller\Admin\LoginController;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Security\LoginAdministratorAsUserUrlProvider as BaseLoginAdministratorAsUserUrlProvider;

class LoginAdministratorAsUserUrlProvider extends BaseLoginAdministratorAsUserUrlProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper
     */
    public function __construct(
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly Domain $domain,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string
     */
    #[Override]
    public function getSsoLoginAsCustomerUserUrl(CustomerUser $customerUser): string
    {
        $userDomainConfig = $this->domain->getDomainConfigById($customerUser->getDomainId());
        $userDomainPostfix = $userDomainConfig->getPostfix();
        $firstDomainConfig = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);
        $firstDomainPostfix = $firstDomainConfig->getPostfix();

        $customerDomainRouter = $this->domainRouterFactory->getRouter($customerUser->getDomainId());

        $loginAsUserUrl = $customerDomainRouter->generate(
            'admin_customeruser_loginascustomeruser',
            [
                'customerUserId' => $customerUser->getId(),
            ],
        );

        if ($userDomainPostfix !== null) {
            $loginAsUserUrl = $this->transformStringHelper->removeStringFromStart(
                $loginAsUserUrl,
                $userDomainPostfix,
            );
        }

        $mainAdminDomainRouter = $this->domainRouterFactory->getRouter(Domain::MAIN_ADMIN_DOMAIN_ID);

        $ssoLoginUrl = $mainAdminDomainRouter->generate(
            'admin_login_sso',
            [
                LoginController::ORIGINAL_DOMAIN_ID_PARAMETER_NAME => $customerUser->getDomainId(),
                LoginController::ORIGINAL_REFERER_PARAMETER_NAME => $loginAsUserUrl,
            ],
        );

        if ($firstDomainPostfix !== null) {
            $ssoLoginUrl = $this->transformStringHelper->removeStringFromStart(
                $ssoLoginUrl,
                $firstDomainPostfix,
            );
        }

        return $firstDomainConfig->getBaseUrl() . $ssoLoginUrl;
    }
}
