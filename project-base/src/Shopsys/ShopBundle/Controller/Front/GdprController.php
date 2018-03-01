<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\ShopBundle\Component\Controller\FrontBaseController;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Component\Setting\Setting;
use Shopsys\ShopBundle\Component\String\HashGenerator;
use Shopsys\ShopBundle\Form\Front\Gdpr\GdprFormType;
use Shopsys\ShopBundle\Model\Customer\CustomerFacade;
use Shopsys\ShopBundle\Model\Gdpr\GdprFacade;
use Shopsys\ShopBundle\Model\Gdpr\Mail\GdprMailFacade;
use Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequestData;
use Shopsys\ShopBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\ShopBundle\Model\Order\OrderFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @var \Shopsys\ShopBundle\Model\Customer\CustomerFacade
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

    /**
     * @var \Shopsys\ShopBundle\Model\Gdpr\Mail\GdprMailFacade
     */
    private $gdprMailFacade;

    /**
     * @var \Shopsys\ShopBundle\Component\String\HashGenerator
     */
    private $hashGenerator;

    /**
     * @var \Shopsys\ShopBundle\Model\Gdpr\GdprFacade
     */
    private $gdprFacade;

    public function __construct(
        Setting $setting,
        Domain $domain,
        CustomerFacade $customerFacade,
        OrderFacade $orderFacade,
        NewsletterFacade $newsletterFacade,
        GdprMailFacade $gdprMailFacade,
        HashGenerator $hashGenerator,
        GdprFacade $gdprFacade
    ) {
        $this->setting = $setting;
        $this->domain = $domain;
        $this->customerFacade = $customerFacade;
        $this->orderFacade = $orderFacade;
        $this->newsletterFacade = $newsletterFacade;
        $this->gdprMailFacade = $gdprMailFacade;
        $this->hashGenerator = $hashGenerator;
        $this->gdprFacade = $gdprFacade;
    }

    public function indexAction(Request $request)
    {
        $personalDataAccessRequestData = new PersonalDataAccessRequestData();

        $form = $this->createForm(GdprFormType::class, $personalDataAccessRequestData);
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            $gdpr = $this->gdprFacade->createPersonalDataAccessRequest($form->getData(), $this->domain->getId());

            $this->gdprMailFacade->sendMail($gdpr);
            $this->getFlashMessageSender()->addSuccessFlash(t('Email with link has sent. Pleas check email !'));
        }
        return $this->render('@ShopsysShop/Front/Content/Gdpr/gdpr.html.twig', [
            'gdprSiteContent' => $this->getGdpirSiteContent(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param string $email
     */
    public function detailAction($hash)
    {
        $personalDataAccessRequest = $this->gdprFacade->findEmailByToken($hash, $this->domain->getId());
        if ($personalDataAccessRequest != null) {
            $user = $this->customerFacade->findUserByEmailAndDomain($personalDataAccessRequest->getEmail(), $this->domain->getId());
            $orders = $this->orderFacade->getOrderListForEmailByDomainId($personalDataAccessRequest->getEmail(), $this->domain->getId());
            $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomain(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId()
            );
            return $this->render('@ShopsysShop/Front/Content/Gdpr/detail.html.twig', [
                'gdprSiteContent' => $this->getGdpirSiteContent(),
                'orders' => $orders,
                'user' => $user,
                'newsletterSubscriber' => $newsletterSubscriber,
            ]);
        }

        throw new NotFoundHttpException();
    }

    /**
     * @return string|null
     */
    private function getGdpirSiteContent()
    {
        return $this->setting->getForDomain(Setting::GDPR_SITE_CONTENT, $this->domain->getId());
    }
}
