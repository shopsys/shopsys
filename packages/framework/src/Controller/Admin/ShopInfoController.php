<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\ShopInfo\ShopInfoSettingFormType;
use Shopsys\FrameworkBundle\Model\ShopInfo\ShopInfoSettingFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ShopInfoController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\ShopInfo\ShopInfoSettingFacade $shopInfoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly ShopInfoSettingFacade $shopInfoSettingFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade
    ) {
    }

    /**
     * @Route("/shop-info/setting/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function settingAction(Request $request)
    {
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $shopInfoSettingData = [
            'phoneNumber' => $this->shopInfoSettingFacade->getPhoneNumber($selectedDomainId),
            'email' => $this->shopInfoSettingFacade->getEmail($selectedDomainId),
            'phoneHours' => $this->shopInfoSettingFacade->getPhoneHours($selectedDomainId),
        ];

        $form = $this->createForm(ShopInfoSettingFormType::class, $shopInfoSettingData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shopInfoSettingData = $form->getData();

            $this->shopInfoSettingFacade->setPhoneNumber($shopInfoSettingData['phoneNumber'], $selectedDomainId);
            $this->shopInfoSettingFacade->setEmail($shopInfoSettingData['email'], $selectedDomainId);
            $this->shopInfoSettingFacade->setPhoneHours($shopInfoSettingData['phoneHours'], $selectedDomainId);

            $this->addSuccessFlash(t('E-shop attributes settings modified'));

            return $this->redirectToRoute('admin_shopinfo_setting');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/ShopInfo/shopInfo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
