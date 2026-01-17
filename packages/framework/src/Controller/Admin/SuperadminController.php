<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\Module\ModulesFormType;
use Shopsys\FrameworkBundle\Form\Admin\Superadmin\InputPriceTypeFormType;
use Shopsys\FrameworkBundle\Form\Admin\Superadmin\MailWhitelistFormType;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Pricing\DelayedPricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SuperAdminOnly]
class SuperadminController extends AdminBaseController
{
    public function __construct(
        protected readonly ModuleList $moduleList,
        protected readonly ModuleFacade $moduleFacade,
        protected readonly PricingSetting $pricingSetting,
        protected readonly DelayedPricingSetting $delayedPricingSetting,
        protected readonly Localization $localization,
        protected readonly LocalizedRouterFactory $localizedRouterFactory,
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly Setting $setting,
    ) {
    }

    #[Route(path: '/superadmin/pricing/')]
    public function pricingAction(Request $request): Response
    {
        $pricingSettingData = [
            'inputPriceType' => $this->pricingSetting->getInputPriceType(),
            'sellingPriceType' => $this->pricingSetting->getSellingPriceType(),
        ];

        $form = $this->createForm(InputPriceTypeFormType::class, $pricingSettingData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pricingSettingData = $form->getData();

            $this->delayedPricingSetting->scheduleSetInputPriceType($pricingSettingData['inputPriceType']);
            $this->setting->set(
                PricingSetting::SELLING_PRICE_TYPE,
                $pricingSettingData['sellingPriceType'],
            );

            $this->addSuccessFlash(t('Pricing settings modified'));

            return $this->redirectToRoute('admin_superadmin_pricing');
        }

        return $this->render('@ShopsysAdministration/content/superadmin/pricing.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/superadmin/urls/')]
    public function urlsAction(): Response
    {
        return $this->render('@ShopsysAdministration/content/superadmin/urlsListGrid.html.twig');
    }

    #[Route(path: '/superadmin/modules/')]
    public function modulesAction(Request $request): Response
    {
        $formData = [];

        foreach ($this->moduleList->getNames() as $moduleName) {
            $formData[$moduleName] = $this->moduleFacade->isEnabled($moduleName);
        }

        $form = $this->createForm(ModulesFormType::class, $formData, ['module_list' => $this->moduleList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            foreach ($formData as $moduleName => $isEnabled) {
                $this->moduleFacade->setEnabled($moduleName, $isEnabled);
            }

            $this->addSuccessFlash(t('Modules configuration modified'));

            return $this->redirectToRoute('admin_superadmin_modules');
        }

        return $this->render('@ShopsysAdministration/content/superadmin/modules.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/superadmin/mail-whitelist')]
    public function mailWhitelistAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $mailWhitelistSettingData = [
            'mailWhitelist' => $this->mailSettingFacade->getMailWhitelist($domainId),
            'mailWhitelistEnabled' => $this->mailSettingFacade->isWhitelistEnabled($domainId),
        ];

        $form = $this->createForm(MailWhitelistFormType::class, $mailWhitelistSettingData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mailWhitelistSettingData = $form->getData();

            $this->mailSettingFacade->setMailWhitelist($mailWhitelistSettingData['mailWhitelist'], $domainId);
            $this->mailSettingFacade->setWhitelistEnabled($mailWhitelistSettingData['mailWhitelistEnabled'], $domainId);

            $this->addSuccessFlash(t('E-mail whitelist settings modified'));

            return $this->redirectToRoute('admin_superadmin_mailwhitelist');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/superadmin/mailWhitelist.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
