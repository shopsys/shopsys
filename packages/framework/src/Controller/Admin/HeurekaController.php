<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Heureka\HeurekaShopCertificationFormType;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_HEUREKA)]
class HeurekaController extends AdminBaseController
{
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly HeurekaSetting $heurekaSetting,
        protected readonly HeurekaFacade $heurekaFacade,
    ) {
    }

    #[Route(path: '/heureka/setting/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function settingAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $domainConfig = $this->adminDomainTabsFacade->getSelectedDomainConfig();
        $locale = $domainConfig->getLocale();
        $formView = null;
        $serverName = $this->heurekaFacade->getServerNameByLocale($locale);

        if ($this->heurekaFacade->isDomainLocaleSupported($locale)) {
            $heurekaShopCertificationData = [
                'heurekaApiKey' => $this->heurekaSetting->getApiKeyByDomainId($domainId),
            ];

            $form = $this->createForm(HeurekaShopCertificationFormType::class, $heurekaShopCertificationData, [
                'server_name' => $serverName,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $heurekaShopCertificationData = $form->getData();

                $this->heurekaSetting->setApiKeyForDomain($heurekaShopCertificationData['heurekaApiKey'], $domainId);

                $this->addSuccessFlash(t('Settings modified.'));
            }
            $formView = $form->createView();
        }

        return $this->render('@ShopsysAdministration/content/heureka/setting.html.twig', [
            'form' => $formView,
            'serverName' => $serverName,
            'selectedDomainConfig' => $domainConfig,
        ]);
    }
}
