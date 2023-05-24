<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSource;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
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
use Symfony\Component\Routing\RequestContext;

class SuperadminController extends AdminBaseController
{
    use SetterInjectionTrait;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\ModuleList
     */
    protected $moduleList;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\ModuleFacade
     */
    protected $moduleFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory
     */
    protected $localizedRouterFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    protected $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    protected $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    protected $pricingSetting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\DelayedPricingSetting
     */
    protected $delayedPricingSetting;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleList $moduleList
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Pricing\DelayedPricingSetting $delayedPricingSetting
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory $localizedRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade|null $mailSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider|null $mailerSettingProvider
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade|null $adminDomainTabsFacade
     */
    public function __construct(
        ModuleList $moduleList,
        ModuleFacade $moduleFacade,
        PricingSetting $pricingSetting,
        DelayedPricingSetting $delayedPricingSetting,
        GridFactory $gridFactory,
        Localization $localization,
        LocalizedRouterFactory $localizedRouterFactory,
        protected /* readonly */ ?MailSettingFacade $mailSettingFacade = null,
        protected /* readonly */ ?MailerSettingProvider $mailerSettingProvider = null,
        protected /* readonly */ ?AdminDomainTabsFacade $adminDomainTabsFacade = null,
    ) {
        $this->moduleList = $moduleList;
        $this->moduleFacade = $moduleFacade;
        $this->pricingSetting = $pricingSetting;
        $this->delayedPricingSetting = $delayedPricingSetting;
        $this->gridFactory = $gridFactory;
        $this->localization = $localization;
        $this->localizedRouterFactory = $localizedRouterFactory;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setMailSettingFacade(MailSettingFacade $mailSettingFacade): void
    {
        $this->setDependency($mailSettingFacade, 'mailSettingFacade');
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider $mailerSettingProvider
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setMailerSettingProvider(MailerSettingProvider $mailerSettingProvider): void
    {
        $this->setDependency($mailerSettingProvider, 'mailerSettingProvider');
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setAdminDomainTabsFacade(AdminDomainTabsFacade $adminDomainTabsFacade): void
    {
        $this->setDependency($adminDomainTabsFacade, 'adminDomainTabsFacade');
    }

    /**
     * @Route("/superadmin/errors/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function errorsAction()
    {
        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/errors.html.twig');
    }

    /**
     * @Route("/superadmin/pricing/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
     * @Route("/superadmin/urls/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function urlsAction()
    {
        $allLocales = $this->localization->getLocalesOfAllDomains();
        $dataSource = new ArrayDataSource($this->loadDataForUrls($allLocales));

        $grid = $this->gridFactory->create('urlsList', $dataSource);

        foreach ($allLocales as $locale) {
            $grid->addColumn($locale, $locale, $this->localization->getLanguageName($locale));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/urlsListGrid.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param array $locales
     * @return array
     */
    protected function loadDataForUrls(array $locales)
    {
        $data = [];
        $requestContext = new RequestContext();

        foreach ($locales as $locale) {
            $rowIndex = 0;
            $allRoutes = $this->localizedRouterFactory->getRouter($locale, $requestContext)
                ->getRouteCollection()
                ->all();

            foreach ($allRoutes as $route) {
                $data[$rowIndex][$locale] = $route->getPath();
                $rowIndex++;
            }
        }

        return $data;
    }

    /**
     * @Route("/superadmin/modules/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
     * @Route("/superadmin/css-documentation/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cssDocumentationAction()
    {
        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/cssDocumentation.html.twig');
    }

    /**
     * @Route("/superadmin/mail-whitelist")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
            'isOverridden' => $this->mailerSettingProvider->isMailerWhitelistExpressionsSet(),
            'isWhitelistForced' => $this->mailerSettingProvider->isWhitelistForced(),
        ]);
    }
}
