<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Order\DomainAwareOrderFlowFactory;
use App\Model\Order\FrontOrderData;
use App\Model\Order\OrderData;
use App\Model\Order\OrderDataMapper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentWatcher;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderController extends FrontBaseController
{
    public const SESSION_CREATED_ORDER = 'created_order_id';

    /**
     * @var \App\Form\Front\Order\DomainAwareOrderFlowFactory
     */
    private $domainAwareOrderFlowFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade
     */
    private $orderMailFacade;

    /**
     * @var \App\Model\Order\OrderDataMapper
     */
    private $orderDataMapper;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory
     */
    private $orderPreviewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentWatcher
     */
    private $transportAndPaymentWatcher;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     */
    private $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    private $paymentPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    private $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    private $transportPriceCalculation;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade
     */
    private $legalConditionsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade
     */
    private $newsletterFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Order\OrderDataMapper $orderDataMapper
     * @param \App\Form\Front\Order\DomainAwareOrderFlowFactory $domainAwareOrderFlowFactory
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentWatcher $transportAndPaymentWatcher
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     */
    public function __construct(
        OrderFacade $orderFacade,
        CartFacade $cartFacade,
        OrderPreviewFactory $orderPreviewFactory,
        TransportPriceCalculation $transportPriceCalculation,
        PaymentPriceCalculation $paymentPriceCalculation,
        Domain $domain,
        TransportFacade $transportFacade,
        PaymentFacade $paymentFacade,
        CurrencyFacade $currencyFacade,
        OrderDataMapper $orderDataMapper,
        DomainAwareOrderFlowFactory $domainAwareOrderFlowFactory,
        SessionInterface $session,
        TransportAndPaymentWatcher $transportAndPaymentWatcher,
        OrderMailFacade $orderMailFacade,
        LegalConditionsFacade $legalConditionsFacade,
        NewsletterFacade $newsletterFacade
    ) {
        $this->orderFacade = $orderFacade;
        $this->cartFacade = $cartFacade;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->transportPriceCalculation = $transportPriceCalculation;
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->domain = $domain;
        $this->transportFacade = $transportFacade;
        $this->paymentFacade = $paymentFacade;
        $this->currencyFacade = $currencyFacade;
        $this->orderDataMapper = $orderDataMapper;
        $this->domainAwareOrderFlowFactory = $domainAwareOrderFlowFactory;
        $this->session = $session;
        $this->transportAndPaymentWatcher = $transportAndPaymentWatcher;
        $this->orderMailFacade = $orderMailFacade;
        $this->legalConditionsFacade = $legalConditionsFacade;
        $this->newsletterFacade = $newsletterFacade;
    }

    public function indexAction()
    {
        $cart = $this->cartFacade->findCartOfCurrentCustomerUser();
        if ($cart === null) {
            return $this->redirectToRoute('front_cart');
        }

        $customerUser = $this->getUser();

        $frontOrderFormData = new FrontOrderData();
        $frontOrderFormData->deliveryAddressSameAsBillingAddress = true;
        if ($customerUser instanceof CustomerUser) {
            $this->orderFacade->prefillFrontOrderData($frontOrderFormData, $customerUser);
        }

        $domainId = $this->domain->getId();
        $frontOrderFormData->domainId = $domainId;
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $frontOrderFormData->currency = $currency;

        $orderFlow = $this->domainAwareOrderFlowFactory->create();
        if ($orderFlow->isBackToCartTransition()) {
            return $this->redirectToRoute('front_cart');
        }

        $orderFlow->bind($frontOrderFormData);
        $orderFlow->saveSentStepData();

        $form = $orderFlow->createForm();

        $payment = $frontOrderFormData->payment;
        $transport = $frontOrderFormData->transport;

        $orderPreview = $this->orderPreviewFactory->createForCurrentUser($transport, $payment);

        $isValid = $orderFlow->isValid($form);
        // FormData are filled during isValid() call
        $orderData = $this->orderDataMapper->getOrderDataFromFrontOrderData($frontOrderFormData);

        /** @var \App\Model\Payment\Payment[] $payments */
        $payments = $this->paymentFacade->getVisibleOnCurrentDomain();
        /** @var \App\Model\Transport\Transport[] $transports */
        $transports = $this->transportFacade->getVisibleOnCurrentDomain($payments);
        $this->checkTransportAndPaymentChanges($orderData, $orderPreview, $transports, $payments);

        if ($isValid) {
            if ($orderFlow->nextStep()) {
                $form = $orderFlow->createForm();
            } elseif (count($this->getErrorMessages()) === 0 && count($this->getInfoMessages()) === 0) {
                $deliveryAddress = $orderData->deliveryAddressSameAsBillingAddress === false ? $frontOrderFormData->deliveryAddress : null;
                /** @var \App\Model\Order\Order $order */
                $order = $this->orderFacade->createOrderFromFront($orderData, $deliveryAddress);
                $this->orderFacade->sendHeurekaOrderInfo($order, $frontOrderFormData->disallowHeurekaVerifiedByCustomers);

                if ($frontOrderFormData->newsletterSubscription) {
                    $this->newsletterFacade->addSubscribedEmail($frontOrderFormData->email, $this->domain->getId());
                }

                $orderFlow->reset();

                try {
                    $this->sendMail($order);
                } catch (\Shopsys\FrameworkBundle\Model\Mail\Exception\MailException $e) {
                    $this->addErrorFlash(
                        t('Unable to send some emails, please contact us for order verification.')
                    );
                }

                $this->session->set(self::SESSION_CREATED_ORDER, $order->getId());

                return $this->redirectToRoute('front_order_sent');
            }
        }

        if ($form->isSubmitted() && !$form->isValid() && $form->getErrors()->count() === 0) {
            $form->addError(new FormError(t('Please check the correctness of all data filled.')));
        }

        return $this->render('Front/Content/Order/index.html.twig', [
            'form' => $form->createView(),
            'flow' => $orderFlow,
            'transport' => $transport,
            'payment' => $payment,
            'payments' => $payments,
            'transportsPrices' => $this->transportPriceCalculation->getCalculatedPricesIndexedByTransportId(
                $transports,
                $currency,
                $orderPreview->getProductsPrice(),
                $domainId
            ),
            'paymentsPrices' => $this->paymentPriceCalculation->getCalculatedPricesIndexedByPaymentId(
                $payments,
                $currency,
                $orderPreview->getProductsPrice(),
                $domainId
            ),
            'termsAndConditionsArticle' => $this->legalConditionsFacade->findTermsAndConditions($this->domain->getId()),
            'privacyPolicyArticle' => $this->legalConditionsFacade->findPrivacyPolicy($this->domain->getId()),
            'paymentTransportRelations' => $this->getPaymentTransportRelations($payments),
        ]);
    }

    /**
     * @param \App\Model\Payment\Payment[] $payments
     * @return string
     */
    private function getPaymentTransportRelations(array $payments): string
    {
        $relations = [];
        foreach ($payments as $payment) {
            foreach ($payment->getTransports() as $transport) {
                $relations[] = [
                    'paymentId' => $payment->getId(),
                    'transportId' => $transport->getId(),
                ];
            }
        }

        return json_encode($relations);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function previewAction(Request $request)
    {
        $transportId = $request->get('transportId');
        $paymentId = $request->get('paymentId');

        if ($transportId === null) {
            $transport = null;
        } else {
            $transport = $this->transportFacade->getById($transportId);
        }

        if ($paymentId === null) {
            $payment = null;
        } else {
            $payment = $this->paymentFacade->getById($paymentId);
        }

        $orderPreview = $this->orderPreviewFactory->createForCurrentUser($transport, $payment);

        return $this->render('Front/Content/Order/preview.html.twig', [
            'orderPreview' => $orderPreview,
        ]);
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param \App\Model\Transport\Transport[] $transports
     * @param \App\Model\Payment\Payment[] $payments
     */
    private function checkTransportAndPaymentChanges(
        OrderData $orderData,
        OrderPreview $orderPreview,
        array $transports,
        array $payments
    ) {
        $transportAndPaymentCheckResult = $this->transportAndPaymentWatcher->checkTransportAndPayment(
            $orderData,
            $orderPreview,
            $transports,
            $payments
        );

        if ($transportAndPaymentCheckResult->isTransportPriceChanged()) {
            $this->addInfoFlashTwig(
                t('The price of shipping {{ transportName }} changed during ordering process. Check your order, please.'),
                [
                    'transportName' => $orderData->transport->getName(),
                ]
            );
        }
        if ($transportAndPaymentCheckResult->isPaymentPriceChanged()) {
            $this->addInfoFlashTwig(
                t('The price of payment {{ paymentName }} changed during ordering process. Check your order, please.'),
                [
                    'paymentName' => $orderData->payment->getName(),
                ]
            );
        }
    }

    public function saveOrderFormAction()
    {
        $flow = $this->domainAwareOrderFlowFactory->create();
        $flow->bind(new FrontOrderData());
        $form = $flow->createForm();
        $flow->saveCurrentStepData($form);

        return new Response();
    }

    public function sentAction()
    {
        $orderId = $this->session->get(self::SESSION_CREATED_ORDER, null);
        $this->session->remove(self::SESSION_CREATED_ORDER);

        if ($orderId === null) {
            return $this->redirectToRoute('front_cart');
        }

        return $this->render('Front/Content/Order/sent.html.twig', [
            'pageContent' => $this->orderFacade->getOrderSentPageContent($orderId),
            'order' => $this->orderFacade->getById($orderId),
        ]);
    }

    public function termsAndConditionsAction()
    {
        return $this->getTermsAndConditionsResponse();
    }

    public function termsAndConditionsDownloadAction()
    {
        $response = $this->getTermsAndConditionsResponse();

        return new DownloadFileResponse(
            $this->legalConditionsFacade->getTermsAndConditionsDownloadFilename(),
            $response->getContent(),
            'text/html'
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getTermsAndConditionsResponse()
    {
        return $this->render('Front/Content/Order/legalConditions.html.twig', [
            'termsAndConditionsArticle' => $this->legalConditionsFacade->findTermsAndConditions($this->domain->getId()),
        ]);
    }

    /**
     * @param \App\Model\Order\Order $order
     */
    private function sendMail($order)
    {
        $mailTemplate = $this->orderMailFacade->getMailTemplateByStatusAndDomainId($order->getStatus(), $order->getDomainId());
        if ($mailTemplate->isSendMail()) {
            $this->orderMailFacade->sendEmail($order);
        }
    }
}
