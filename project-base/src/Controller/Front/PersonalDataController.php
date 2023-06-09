<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\PersonalData\PersonalDataFormType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\XmlResponse;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMailFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PersonalDataController extends FrontBaseController
{
    private Setting $setting;

    private Domain $domain;

    private CustomerUserFacade $customerUserFacade;

    private OrderFacade $orderFacade;

    private NewsletterFacade $newsletterFacade;

    private PersonalDataAccessRequestFacade $personalDataAccessRequestFacade;

    private PersonalDataAccessMailFacade $personalDataAccessMailFacade;

    private PersonalDataAccessRequestDataFactoryInterface $personalDataAccessRequestDataFactory;

    private XmlResponse $xmlResponse;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMailFacade $personalDataAccessMailFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade $personalDataAccessRequestFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactoryInterface $personalDataAccessRequestDataFactory
     * @param \Shopsys\FrameworkBundle\Component\HttpFoundation\XmlResponse $xmlResponse
     */
    public function __construct(
        Setting $setting,
        Domain $domain,
        CustomerUserFacade $customerUserFacade,
        OrderFacade $orderFacade,
        NewsletterFacade $newsletterFacade,
        PersonalDataAccessMailFacade $personalDataAccessMailFacade,
        PersonalDataAccessRequestFacade $personalDataAccessRequestFacade,
        PersonalDataAccessRequestDataFactoryInterface $personalDataAccessRequestDataFactory,
        XmlResponse $xmlResponse
    ) {
        $this->setting = $setting;
        $this->domain = $domain;
        $this->customerUserFacade = $customerUserFacade;
        $this->orderFacade = $orderFacade;
        $this->newsletterFacade = $newsletterFacade;
        $this->personalDataAccessMailFacade = $personalDataAccessMailFacade;
        $this->personalDataAccessRequestFacade = $personalDataAccessRequestFacade;
        $this->personalDataAccessRequestDataFactory = $personalDataAccessRequestDataFactory;
        $this->xmlResponse = $xmlResponse;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(
            PersonalDataFormType::class,
            $this->personalDataAccessRequestDataFactory->createForDisplay()
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personalData = $this->personalDataAccessRequestFacade->createPersonalDataAccessRequest(
                $form->getData(),
                $this->domain->getId()
            );
            $this->personalDataAccessMailFacade->sendMail($personalData);
            $this->addSuccessFlash(
                t('Email with a link to the page with your personal data was sent to your email address.')
            );
        }

        $content = $this->setting->getForDomain(Setting::PERSONAL_DATA_DISPLAY_SITE_CONTENT, $this->domain->getId());

        return $this->render('Front/Content/PersonalData/index.html.twig', [
            'personalDataSiteContent' => $content,
            'title' => t('Personal information overview'),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function exportAction(Request $request)
    {
        $form = $this->createForm(
            PersonalDataFormType::class,
            $this->personalDataAccessRequestDataFactory->createForExport()
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personalData = $this->personalDataAccessRequestFacade->createPersonalDataAccessRequest(
                $form->getData(),
                $this->domain->getId()
            );
            $this->personalDataAccessMailFacade->sendMail($personalData);
            $this->addSuccessFlash(
                t('Email with a link to the export of your personal data was sent to your email address.')
            );
        }

        $content = $this->setting->getForDomain(Setting::PERSONAL_DATA_EXPORT_SITE_CONTENT, $this->domain->getId());

        return $this->render('Front/Content/PersonalData/index.html.twig', [
            'personalDataSiteContent' => $content,
            'title' => t('Personal information export'),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param string $hash
     */
    public function accessDisplayAction($hash)
    {
        $personalDataAccessRequest = $this->personalDataAccessRequestFacade->findByHashAndDomainId(
            $hash,
            $this->domain->getId()
        );

        if (
            $personalDataAccessRequest !== null
            && $personalDataAccessRequest->getType() === PersonalDataAccessRequest::TYPE_DISPLAY
        ) {
            $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId()
            );
            $orders = $this->orderFacade->getOrderListForEmailByDomainId(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId()
            );
            $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId()
            );

            return $this->render('Front/Content/PersonalData/detail.html.twig', [
                'personalDataAccessRequest' => $personalDataAccessRequest,
                'orders' => $orders,
                'customerUser' => $customerUser,
                'newsletterSubscriber' => $newsletterSubscriber,
            ]);
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param string $hash
     */
    public function accessExportAction($hash)
    {
        $personalDataAccessRequest = $this->personalDataAccessRequestFacade->findByHashAndDomainId(
            $hash,
            $this->domain->getId()
        );

        if (
            $personalDataAccessRequest !== null
            && $personalDataAccessRequest->getType() === PersonalDataAccessRequest::TYPE_EXPORT
        ) {
            $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId()
            );

            $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId()
            );

            $ordersCount = $this->orderFacade->getOrdersCountByEmailAndDomainId(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId()
            );

            return $this->render('Front/Content/PersonalData/export.html.twig', [
                'personalDataAccessRequest' => $personalDataAccessRequest,
                'domainName' => $this->domain->getName(),
                'hash' => $hash,
                'customerUser' => $customerUser,
                'newsletterSubscriber' => $newsletterSubscriber,
                'ordersCount' => $ordersCount,
            ]);
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param string $hash
     */
    public function exportXmlAction($hash)
    {
        $personalDataAccessRequest = $this->personalDataAccessRequestFacade->findByHashAndDomainId(
            $hash,
            $this->domain->getId()
        );

        if (
            $personalDataAccessRequest !== null
            && $personalDataAccessRequest->getType() === PersonalDataAccessRequest::TYPE_EXPORT
        ) {
            $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId()
            );

            $orders = $this->orderFacade->getOrderListForEmailByDomainId(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId()
            );

            $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId()
            );

            $xmlContent = $this->render('Front/Content/PersonalData/export.xml.twig', [
                'customerUser' => $customerUser,
                'newsletterSubscriber' => $newsletterSubscriber,
                'orders' => $orders,
            ])->getContent();

            $fileName = $personalDataAccessRequest->getEmail() . '.xml';

            return $this->xmlResponse->getXmlResponse($fileName, $xmlContent);
        }

        throw new NotFoundHttpException();
    }
}
