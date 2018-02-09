<?php

namespace Shopsys\ShopBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\ShopBundle\Component\Controller\AdminBaseController;
use Shopsys\ShopBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\ShopBundle\Component\Setting\Setting;
use Shopsys\ShopBundle\Form\Admin\Gdpr\GdprFormType;
use Symfony\Component\HttpFoundation\Request;

class GdprController extends AdminBaseController
{
    /**
     * @var \Shopsys\ShopBundle\Component\Domain\AdminDomainTabsFacade
     */
    private $adminDomainTabsFacade;

    /**
     * @var \ShopSys\ShopBundle\Component\Setting\Setting
     */
    private $setting;

    public function __construct(
        AdminDomainTabsFacade $adminDomainTabsFacade,
        Setting $setting
    ) {
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
        $this->setting = $setting;
    }

    /**
     * @Route("/gdpr/setting/")
     */
    public function settingAction(Request $request)
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $gdprSiteContent = $this->setting->getForDomain(Setting::GDPR_SITE_CONTENT, $domainId);
        $form = $this->createForm(GdprFormType::class, ['content' => $gdprSiteContent]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->setting->setForDomain(Setting::GDPR_SITE_CONTENT, $form->getData()['content'], $domainId);
            $this->getFlashMessageSender()->addSuccessFlash('GDPR content updated successfully');
        }

        return $this->render('@ShopsysShop/Admin/Content/Gdpr/gdpr.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
