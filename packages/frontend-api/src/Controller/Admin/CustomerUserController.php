<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeTokenFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerUserController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeTokenFacade $loginAsUserExchangeTokenFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        protected readonly LoginAsUserExchangeTokenFacade $loginAsUserExchangeTokenFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @CsrfProtection
     * @param int $customerUserId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/login-as-customer-user/{customerUserId}')]
    #[CanEdit('ROLE_CUSTOMER')]
    public function loginAsCustomerUserAction(int $customerUserId): Response
    {
        try {
            $customerUser = $this->customerUserFacade->getCustomerUserById($customerUserId);
            $unencryptedToken = $this->loginAsUserExchangeTokenFacade->createAndGetUnencryptedToken($customerUser, $this->getCurrentAdministrator());

            return new RedirectResponse($this->getStorefrontUrl($customerUser->getDomainId(), $unencryptedToken));
        } catch (CustomerUserNotFoundException) {
            $this->addErrorFlash(t('Customer not found.'));

            return $this->redirectToRoute('admin_customer_list');
        }
    }

    /**
     * @param int $domainId
     * @param string $exchangeToken
     * @return string
     */
    protected function getStorefrontUrl(int $domainId, string $exchangeToken): string
    {
        return $this->domainRouterFactory->getRouter($domainId)->generate(
            'front_homepage',
            ['exchangeToken' => $exchangeToken],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
