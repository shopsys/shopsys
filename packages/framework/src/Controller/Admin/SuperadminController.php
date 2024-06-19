<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory;
use Shopsys\FrameworkBundle\Form\Admin\Module\ModulesFormType;
use Shopsys\FrameworkBundle\Form\Admin\Superadmin\InputPriceTypeFormType;
use Shopsys\FrameworkBundle\Form\Admin\Superadmin\MailWhitelistFormType;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Pricing\DelayedPricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuperadminController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleList $moduleList
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Pricing\DelayedPricingSetting $delayedPricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory $localizedRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider $mailerSettingProvider
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly ModuleList $moduleList,
        protected readonly ModuleFacade $moduleFacade,
        protected readonly PricingSetting $pricingSetting,
        protected readonly DelayedPricingSetting $delayedPricingSetting,
        protected readonly Localization $localization,
        protected readonly LocalizedRouterFactory $localizedRouterFactory,
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly MailerSettingProvider $mailerSettingProvider,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/errors/')]
    public function errorsAction()
    {
        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/errors.html.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/pricing/')]
    public function pricingAction(Request $request)
    {
        $pricingSettingData = [
            'type' => $this->pricingSetting->getInputPriceType(),
        ];

        $form = $this->createForm(InputPriceTypeFormType::class, $pricingSettingData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pricingSettingData = $form->getData();

            $this->delayedPricingSetting->scheduleSetInputPriceType($pricingSettingData['type']);

            $this->addSuccessFlash(t('Pricing settings modified'));

            return $this->redirectToRoute('admin_superadmin_pricing');
        }

        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/pricing.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/urls/')]
    public function urlsAction()
    {
        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/urlsListGrid.html.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/modules/')]
    public function modulesAction(Request $request)
    {
        $formData = [];

        foreach ($this->moduleList->getNames() as $moduleName) {
            $formData['modules'][$moduleName] = $this->moduleFacade->isEnabled($moduleName);
        }

        $form = $this->createForm(ModulesFormType::class, $formData, ['module_list' => $this->moduleList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            foreach ($formData['modules'] as $moduleName => $isEnabled) {
                $this->moduleFacade->setEnabled($moduleName, $isEnabled);
            }

            $this->addSuccessFlash(t('Modules configuration modified'));

            return $this->redirectToRoute('admin_superadmin_modules');
        }

        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/modules.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/css-documentation/')]
    public function cssDocumentationAction()
    {
        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/cssDocumentation.html.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/mailWhitelist.html.twig', [
            'form' => $form->createView(),
            'isWhitelistForced' => $this->mailerSettingProvider->isWhitelistForced(),
        ]);
    }
}
