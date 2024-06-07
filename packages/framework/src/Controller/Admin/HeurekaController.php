<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\Heureka\HeurekaShopCertificationFormType;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HeurekaController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting $heurekaSetting
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     */
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly HeurekaSetting $heurekaSetting,
        protected readonly HeurekaFacade $heurekaFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/heureka/setting/')]
    public function settingAction(Request $request)
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $domainConfig = $this->adminDomainTabsFacade->getSelectedDomainConfig();
        $locale = $domainConfig->getLocale();
        $formView = null;
        $serverName = $this->heurekaFacade->getServerNameByLocale($locale);

        if ($this->heurekaFacade->isDomainLocaleSupported($locale)) {
            $heurekaShopCertificationData = [
                'heurekaApiKey' => $this->heurekaSetting->getApiKeyByDomainId($domainId),
                'heurekaWidgetCode' => $this->heurekaSetting->getHeurekaShopCertificationWidgetByDomainId($domainId),
            ];

            $form = $this->createForm(HeurekaShopCertificationFormType::class, $heurekaShopCertificationData, [
                'server_name' => $serverName,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $heurekaShopCertificationData = $form->getData();

                $this->heurekaSetting->setApiKeyForDomain($heurekaShopCertificationData['heurekaApiKey'], $domainId);
                $this->heurekaSetting->setHeurekaShopCertificationWidgetForDomain(
                    $heurekaShopCertificationData['heurekaWidgetCode'],
                    $domainId,
                );

                $this->addSuccessFlash(t('Settings modified.'));
            }
            $formView = $form->createView();
        }

        return $this->render('@ShopsysFramework/Admin/Content/Heureka/setting.html.twig', [
            'form' => $formView,
            'serverName' => $serverName,
            'selectedDomainConfig' => $domainConfig,
        ]);
    }
}
