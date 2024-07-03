<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Security\Roles;
use Shopsys\FrameworkBundle\Controller\Admin\AdministratorController as BaseAdministratorController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @property \App\Model\Administrator\AdministratorFacade $administratorFacade
 * @property \App\Model\Administrator\AdministratorDataFactory $administratorDataFactory
 * @method \App\Model\Administrator\Administrator getCurrentAdministrator()
 * @property \App\Model\Administrator\AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade
 * @method __construct(\App\Model\Administrator\AdministratorFacade $administratorFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade $administratorActivityFacade, \App\Model\Administrator\AdministratorDataFactory $administratorDataFactory, \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedFacade $administratorRolesChangedFacade, \App\Model\Administrator\AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade)
 * @method \Symfony\Component\HttpFoundation\Response enableEmailTwoFactorAuthentication(\Symfony\Component\HttpFoundation\Request $request, \App\Model\Administrator\Administrator $administrator)
 * @method \Symfony\Component\HttpFoundation\Response enableGoogleAuthTwoFactorAuthentication(\Symfony\Component\HttpFoundation\Request $request, \App\Model\Administrator\Administrator $administrator)
 */
class AdministratorController extends BaseAdministratorController
{
    /**
     * {@inheritdoc}
     */
    public function editAction(Request $request, int $id)
    {
        $this->denyAccessUnlessHimselfOrGranted($request, $id);

        return parent::editAction($request, $id);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $administratorId
     */
    private function denyAccessUnlessHimselfOrGranted(Request $request, int $administratorId): void
    {
        /** @var \App\Model\Administrator\Administrator $currentAdministrator */
        $currentAdministrator = $this->getUser();

        // always allow admin to edit himself
        if ($currentAdministrator->getId() === $administratorId) {
            return;
        }

        if ($request->getMethod() === Request::METHOD_GET) {
            $this->denyAccessUnlessGranted(Roles::ROLE_ADMINISTRATOR_VIEW);
        } else {
            $this->denyAccessUnlessGranted(Roles::ROLE_ADMINISTRATOR_FULL);
        }
    }
}
