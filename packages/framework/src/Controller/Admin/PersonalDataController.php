<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\PersonalData\PersonalDataFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_PERSONAL_DATA)]
class PersonalDataController extends AdminBaseController
{
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly Setting $setting,
    ) {
    }

    #[Route(path: '/personal-data/setting/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function settingAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $personalDataDisplaySiteContent = $this->setting->getForDomain(
            Setting::PERSONAL_DATA_DISPLAY_SITE_CONTENT,
            $domainId,
        );
        $personalDataExportSiteContent = $this->setting->getForDomain(
            Setting::PERSONAL_DATA_EXPORT_SITE_CONTENT,
            $domainId,
        );

        $form = $this->createForm(
            PersonalDataFormType::class,
            [
                'personalDataDisplaySiteContent' => $personalDataDisplaySiteContent,
                'personalDataExportSiteContent' => $personalDataExportSiteContent,
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->setting->setForDomain(
                Setting::PERSONAL_DATA_DISPLAY_SITE_CONTENT,
                $form->getData()['personalDataDisplaySiteContent'],
                $domainId,
            );
            $this->setting->setForDomain(
                Setting::PERSONAL_DATA_EXPORT_SITE_CONTENT,
                $form->getData()['personalDataExportSiteContent'],
                $domainId,
            );
            $this->addSuccessFlash(t('Personal data site content updated successfully'));
        }

        return $this->render('@ShopsysAdministration/content/personalData/index.html.twig', [
            'form' => $form->createView(),
            'domainId' => $domainId,
        ]);
    }
}
