<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\PersonalData\PersonalDataFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PersonalDataController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly Setting $setting,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/personal-data/setting/')]
    public function settingAction(Request $request)
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

        return $this->render('@ShopsysFramework/Admin/Content/PersonalData/index.html.twig', [
            'form' => $form->createView(),
            'domainId' => $domainId,
        ]);
    }
}
