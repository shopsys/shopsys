<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\AdministratorController as BaseAdministratorController;

/**
 * @property \App\Model\Administrator\AdministratorFacade $administratorFacade
 * @property \App\Model\Administrator\AdministratorDataFactory $administratorDataFactory
 * @method \App\Model\Administrator\Administrator getCurrentAdministrator()
 * @property \App\Model\Administrator\AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade
 * @method __construct(\App\Model\Administrator\AdministratorFacade $administratorFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade $administratorActivityFacade, \App\Model\Administrator\AdministratorDataFactory $administratorDataFactory, \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedFacade $administratorRolesChangedFacade, \App\Model\Administrator\AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorPasswordFacade $administratorPasswordFacade, \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator)
 * @method \Symfony\Component\HttpFoundation\Response enableEmailTwoFactorAuthentication(\Symfony\Component\HttpFoundation\Request $request, \App\Model\Administrator\Administrator $administrator)
 * @method \Symfony\Component\HttpFoundation\Response enableGoogleAuthTwoFactorAuthentication(\Symfony\Component\HttpFoundation\Request $request, \App\Model\Administrator\Administrator $administrator)
 * @property \App\Model\Administrator\AdministratorRepository $administratorRepository
 * @property \App\FrontendApi\Model\Token\TokenAuthenticator $tokenAuthenticator
 */
class AdministratorController extends BaseAdministratorController
{
}
