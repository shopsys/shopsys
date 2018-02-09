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
     * @Route("/gdpr/settings/")
     */
    public function settingsAction(Request $request)
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $orderSentPageContent = $this->setting->getForDomain(Setting::GDPR_SITE_CONTENT, $domainId);
        $form = $this->createForm(GdprFormType::class, ['content' => $orderSentPageContent]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->setting->setForDomain(Setting::GDPR_SITE_CONTENT, $form->getData()['content'], $domainId);
            $this->getFlashMessageSender()->addSuccessFlash('GDPR content update success');
        }
        return $this->render('@ShopsysShop/Admin/Content/Gdpr/gdpr.html.twig', [
            'form' => $form->createView(),
            'domain' => $domainId,
        ]);
    }
}
