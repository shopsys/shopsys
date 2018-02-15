<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\ShopBundle\Component\Controller\FrontBaseController;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Component\Setting\Setting;
use Shopsys\ShopBundle\Form\Front\Gdpr\GdprFormType;
use Shopsys\ShopBundle\Model\Customer\CustomerFacade;
use Shopsys\ShopBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\ShopBundle\Model\Order\OrderFacade;
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

    /**
     * @var \Shopsys\ShopBundle\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Newsletter\NewsletterFacade
     */
    private $newsletterFacade;

    public function __construct(
        Setting $setting,
        Domain $domain,
        CustomerFacade $customerFacade,
        OrderFacade $orderFacade,
        NewsletterFacade $newsletterFacade
    ) {
        $this->setting = $setting;
        $this->domain = $domain;
        $this->customerFacade = $customerFacade;
        $this->orderFacade = $orderFacade;
        $this->newsletterFacade = $newsletterFacade;
    }

    public function indexAction(Request $request)
    {
        $gdprContent = $this->setting->getForDomain(Setting::GDPR_SITE_CONTENT, $this->domain->getId());
        $form = $this->createForm(GdprFormType::class);
        $form->handleRequest($request);
        $userOrders = null;
        $emailOrders = null;
        $newsletterSubscribe = null;
        $user = null;

        if ($form->isValid()) {
            $user = $this->customerFacade->findUserByEmailAndDomain($form->getData()['email'], $this->domain->getId());
            if ($user) {
                $userOrders = $this->orderFacade->getCustomerOrderListByDomain($user, $this->domain->getId());
            }

            $emailOrders = $this->orderFacade->getEmailOrderListByDomain($form->getData()['email'], $this->domain->getId());
            $newsletterSubscribe = $this->newsletterFacade->getNewsletterSubscriberByEmail($form->getData()['email']);
        }

        return $this->render('@ShopsysShop/Front/Content/Gdpr/gdpr.html.twig', [
            'gdprContent' => $gdprContent,
            'form' => $form->createView(),
            'userOrders' => $userOrders,
            'emailOrders' => $emailOrders,
            'customerData' => $user,
            'subscribeNewsletter' => $newsletterSubscribe,
        ]);
    }
}
