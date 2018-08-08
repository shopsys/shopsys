<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSource;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory;
use Shopsys\FrameworkBundle\Form\Admin\Module\ModulesFormType;
use Shopsys\FrameworkBundle\Form\Admin\Superadmin\InputPriceTypeFormType;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Pricing\DelayedPricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

class SuperadminController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\ModuleList
     */
    private $moduleList;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\ModuleFacade
     */
    private $moduleFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory
     */
    private $localizedRouterFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    private $pricingSetting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\DelayedPricingSetting
     */
    private $delayedPricingSetting;

    public function __construct(
        ModuleList $moduleList,
        ModuleFacade $moduleFacade,
        PricingSetting $pricingSetting,
        DelayedPricingSetting $delayedPricingSetting,
        GridFactory $gridFactory,
        Localization $localization,
        LocalizedRouterFactory $localizedRouterFactory
    ) {
        $this->moduleList = $moduleList;
        $this->moduleFacade = $moduleFacade;
        $this->pricingSetting = $pricingSetting;
        $this->delayedPricingSetting = $delayedPricingSetting;
        $this->gridFactory = $gridFactory;
        $this->localization = $localization;
        $this->localizedRouterFactory = $localizedRouterFactory;
    }

    public function errorsAction()
    {
        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/errors.html.twig');
    }

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

            $this->getFlashMessageSender()->addSuccessFlash(t('Pricing settings modified'));
            return $this->redirectToRoute('admin_superadmin_pricing');
        }

        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/pricing.html.twig', [
            'form' => $form->createView(),
        ]);
    }

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

    private function loadDataForUrls(array $locales): array
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

            $this->getFlashMessageSender()->addSuccessFlash(t('Modules configuration modified'));
            return $this->redirectToRoute('admin_superadmin_modules');
        }

        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/modules.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function cssDocumentationAction()
    {
        return $this->render('@ShopsysFramework/Admin/Content/Superadmin/cssDocumentation.html.twig');
    }
}
