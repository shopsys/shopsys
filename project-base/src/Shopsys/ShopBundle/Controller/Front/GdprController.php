<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\ShopBundle\Component\Controller\FrontBaseController;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Component\Setting\Setting;
use Shopsys\ShopBundle\Form\Front\Gdpr\GdprFormType;
use Shopsys\ShopBundle\Model\Customer\CustomerFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;

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

    /**
     * @param \Shopsys\ShopBundle\Component\Setting\Setting $setting
     * @param \Shopsys\ShopBundle\Component\Domain\Domain $domain
     * @param \Shopsys\ShopBundle\Model\Customer\CustomerFacade $customerFacade
     */
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
        $form = $this->createForm(GdprFormType::class);
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            return $this->redirectToRoute('front_gdpr_detail', ['email' => $form->getData()['email']]);
        }
        return $this->render('@ShopsysShop/Front/Content/Gdpr/gdpr.html.twig', [
            'gdprSiteContent' => $this->getGdpirSiteContent(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param string $email
     */
    public function detailAction($email)
    {
        $form = $this->createForm(GdprFormType::class, ['email' => $email]);
        $user = $this->customerFacade->findUserByEmailAndDomain($email, $this->domain->getId());
        return $this->render('@ShopsysShop/Front/Content/Gdpr/gdpr.html.twig', [
                'gdprSiteContent' => $this->getGdpirSiteContent(),
                'form' => $form->createView(),
                'user' => $user,
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
