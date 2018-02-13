<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\ShopBundle\Component\Controller\FrontBaseController;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Component\Setting\Setting;
use Shopsys\ShopBundle\Form\Front\Gdpr\GdprFormType;

class GdprController extends FrontBaseController
{
    /**
     * @var \Shopsys\ShopBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Shopsys\ShopBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Shopsys\ShopBundle\Component\Setting\Setting $setting
     * @param \Shopsys\ShopBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        Setting $setting,
        Domain $domain
    ) {
        $this->setting = $setting;
        $this->domain = $domain;
    }

    public function indexAction()
    {
        $form = $this->createForm(GdprFormType::class);

        return $this->render('@ShopsysShop/Front/Content/Gdpr/gdpr.html.twig', [
            'gdprSiteContent' => $this->getGdpirSiteContent(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return string|null
     */
    private function getGdpirSiteContent()
    {
        return $this->setting->getForDomain(Setting::GDPR_SITE_CONTENT, $this->domain->getId());
    }
}
