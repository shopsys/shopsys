<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\ShopBundle\Component\Controller\FrontBaseController;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Component\Setting\Setting;
use Shopsys\ShopBundle\Form\Front\Gdpr\GdprFormType;
use Shopsys\ShopBundle\Model\Customer\CustomerFacade;
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

    /**
     * @var \ShopSys\ShopBundle\Model\Customer\CustomerFacade
     */
    private $customerFacade;

    public function __construct(
        Setting $setting,
        Domain $domain,
        CustomerFacade $customerFacade
    ) {
        $this->setting = $setting;
        $this->domain = $domain;
        $this->customerFacade = $customerFacade;
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

    public function listAction(Request $request)
    {
        $gdprContent = $this->setting->getForDomain(Setting::GDPR_SITE_CONTENT, $this->domain->getId());
        $form = $this->createForm(GdprFormType::class);
        $form->handleRequest($request);

        if ($form->isValid() and $form->isSubmitted()) {
            $user = $this->customerFacade->findUserByEmailAndDomain($form->getData()['email'], $this->domain->getId());
        }

        return $this->render('@ShopsysShop/Front/Content/Gdpr/gdpr-list.html.twig', [
            'gdprContent' => $gdprContent,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
