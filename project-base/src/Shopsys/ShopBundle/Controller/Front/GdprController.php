<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\ShopBundle\Component\Controller\FrontBaseController;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Component\Setting\Setting;
use Shopsys\ShopBundle\Form\Front\Gdpr\GdprFormType;
use Symfony\Component\HttpFoundation\Request;

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

    public function __construct(
        Setting $setting,
        Domain $domain
    ) {
        $this->setting = $setting;
        $this->domain = $domain;
    }

    public function indexAction(Request $request)
    {
        $gdprContent = $this->setting->getForDomain(Setting::GDPR_SITE_CONTENT, $this->domain->getId());
        $form = $this->createForm(GdprFormType::class);

        return $this->render('@ShopsysShop/Front/Content/Gdpr/gdpr.html.twig', [
            'gdprContent' => $gdprContent,
            'form' => $form->createView(),
        ]);
    }
}
