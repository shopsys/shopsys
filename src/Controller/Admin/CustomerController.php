<?php

declare(strict_types=1);


namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\CustomerController as BaseCustomerController;
use Shopsys\FrameworkBundle\Controller\Admin\LoginController;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @property \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @method __construct(\App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade $customerUserListAdminFacade, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \App\Model\Order\OrderFacade $orderFacade, \Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade $loginAsUserFacade, \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory)
 */
class CustomerController extends BaseCustomerController
{
    protected function getSsoLoginAsCustomerUserUrl(CustomerUser $customerUser)
    {
        $loginAsUserUrl = $this->generateUrl(
            'admin_customer_loginasuser',
            [
                'customerUserId' => $customerUser->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $ssoLoginAsUserUrl = $this->generateUrl(
            'admin_login_sso',
            [
                LoginController::ORIGINAL_DOMAIN_ID_PARAMETER_NAME => $customerUser->getDomainId(),
                LoginController::ORIGINAL_REFERER_PARAMETER_NAME => $loginAsUserUrl,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $ssoLoginAsUserUrl;
    }
}
