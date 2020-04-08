<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Order\DomainAwareOrderFlowFactory;
use App\Model\Order\FrontOrderData;
use App\Model\Order\OrderDataFactory;
use App\Model\Order\OrderDataMapper;
use App\Model\Order\Preview\OrderPreviewSplittingFacade;
use App\Model\Order\Preview\SplitOrderPreview;
use App\Model\Order\Watcher\SplitTransportAndPaymentWatcher;
use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
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
     * @var \App\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @var \App\Model\Order\Watcher\SplitTransportAndPaymentWatcher
     */
    private $splitTransportAndPaymentWatcher;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     */
    private $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    private $transportFacade;

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
     * @var \App\Model\Order\Preview\OrderPreviewSplittingFacade
     */
    private $orderPreviewSplittingFacade;

    /**
     * @var \App\Model\Order\OrderDataFactory
     */
    private $orderDataFactory;

    /**
     * @var \App\Model\Stock\StockFacade
     */
    private $stockFacade;

    /**
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Order\OrderDataMapper $orderDataMapper
     * @param \App\Form\Front\Order\DomainAwareOrderFlowFactory $domainAwareOrderFlowFactory
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \App\Model\Order\Watcher\SplitTransportAndPaymentWatcher $splitTransportAndPaymentWatcher
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \App\Model\Order\Preview\OrderPreviewSplittingFacade $orderPreviewSplittingFacade
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     * @param \App\Model\Stock\StockFacade $stockFacade
     */
    public function __construct(
        OrderFacade $orderFacade,
        CartFacade $cartFacade,
        Domain $domain,
        TransportFacade $transportFacade,
        PaymentFacade $paymentFacade,
        CurrencyFacade $currencyFacade,
        OrderDataMapper $orderDataMapper,
        DomainAwareOrderFlowFactory $domainAwareOrderFlowFactory,
        SessionInterface $session,
        SplitTransportAndPaymentWatcher $splitTransportAndPaymentWatcher,
        OrderMailFacade $orderMailFacade,
        LegalConditionsFacade $legalConditionsFacade,
        NewsletterFacade $newsletterFacade,
        OrderPreviewSplittingFacade $orderPreviewSplittingFacade,
        OrderDataFactory $orderDataFactory,
        StockFacade $stockFacade
    ) {
        $this->orderFacade = $orderFacade;
        $this->cartFacade = $cartFacade;
        $this->domain = $domain;
        $this->transportFacade = $transportFacade;
        $this->paymentFacade = $paymentFacade;
        $this->currencyFacade = $currencyFacade;
        $this->orderDataMapper = $orderDataMapper;
        $this->domainAwareOrderFlowFactory = $domainAwareOrderFlowFactory;
        $this->session = $session;
        $this->splitTransportAndPaymentWatcher = $splitTransportAndPaymentWatcher;
        $this->orderMailFacade = $orderMailFacade;
        $this->legalConditionsFacade = $legalConditionsFacade;
        $this->newsletterFacade = $newsletterFacade;
        $this->orderPreviewSplittingFacade = $orderPreviewSplittingFacade;
        $this->orderDataFactory = $orderDataFactory;
        $this->stockFacade = $stockFacade;
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
        $isCompanyCustomer = false;
        $isWithoutRegistration = true;
        if ($customerUser instanceof CustomerUser) {
            $this->orderFacade->prefillFrontOrderData($frontOrderFormData, $customerUser);
            $isCompanyCustomer = $customerUser->getCustomer()->getBillingAddress()->isCompanyCustomer();
            $isWithoutRegistration = false;
        }

        $domainId = $this->domain->getId();
        $frontOrderFormData->domainId = $domainId;
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $frontOrderFormData->currency = $currency;

        $frontOrderFormData->transportsByProductTypeId = [];
        foreach ($this->orderPreviewSplittingFacade->getUsedProductTypesInCurrentCart() as $productType) {
            $frontOrderFormData->transportsByProductTypeId[$productType->getId()] = null;
        }

        $orderFlow = $this->domainAwareOrderFlowFactory->create();
        if ($orderFlow->isBackToCartTransition()) {
            return $this->redirectToRoute('front_cart');
        }
        $orderFlow->setIsCompanyCustomer($isCompanyCustomer);
        $orderFlow->bind($frontOrderFormData);
        $orderFlow->saveSentStepData();

        $form = $orderFlow->createForm();
        $isValid = $orderFlow->isValid($form);
        // FormData are filled during isValid() call
        $orderData = $this->orderDataMapper->getOrderDataFromFrontOrderData($frontOrderFormData);
        $splitOrderPreview = $this->orderPreviewSplittingFacade->createSplitOrderPreviewForCurrentCustomer($orderData);

        $payments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $transports = $this->transportFacade->getVisibleOnCurrentDomain($payments);
        $stocksById = $this->stockFacade->getStocksWithoutCentralByDomainIdIndexedByStockId($domainId);

        $this->checkTransportAndPaymentChanges($frontOrderFormData, $splitOrderPreview);

        if ($isValid) {
            if ($orderFlow->nextStep()) {
                $form = $orderFlow->createForm();
            } elseif ($splitOrderPreview->areAllTransportsSet() === false) {
                $this->addInfoFlash(
                    t('Došlo ke změně v košíku, která vyžaduje, aby jste překontrolovali dopravu objednávky.')
                );
                return $this->redirectToRoute('front_order_index');
            } elseif ($this->isFlashMessageBagEmpty()) {
                $deliveryAddress = $orderData->deliveryAddressSameAsBillingAddress === false ? $frontOrderFormData->deliveryAddress : null;
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
            'splitOrderPreview' => $splitOrderPreview,
            'payments' => $payments,
            'transports' => $transports,
            'stocksById' => $stocksById,
            'termsAndConditionsArticle' => $this->legalConditionsFacade->findTermsAndConditions($this->domain->getId()),
            'privacyPolicyArticle' => $this->legalConditionsFacade->findPrivacyPolicy($this->domain->getId()),
            'paymentTransportRelations' => $this->getPaymentTransportRelations($payments),
            'isWithoutRegistration' => $isWithoutRegistration,
            'isCompanyCustomer' => $isCompanyCustomer,
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
        $transportIdsByProductTypeId = $request->get('transportIdsByProductTypeId');
        $paymentId = $request->get('paymentId');
        $showProducts = $request->get('showProducts', true);
        $showAdvert = $request->get('showAdvert', true);

        $orderData = $this->orderDataFactory->create();

        if (is_array($transportIdsByProductTypeId) === false) {
            $transportIdsByProductTypeId = [];
        }

        $orderData->transportsByProductTypeId = [];
        foreach ($transportIdsByProductTypeId as $productTypeId => $transportId) {
            /** @var \App\Model\Transport\Transport $transport */
            $transport = $this->transportFacade->getById($transportId);
            $orderData->transportsByProductTypeId[$productTypeId] = $transport;
        }

        if ($paymentId === null) {
            $orderData->payment = null;
        } else {
            /** @var \App\Model\Payment\Payment $payment */
            $payment = $this->paymentFacade->getById($paymentId);
            $orderData->payment = $payment;
        }

        $splitOrderPreview = $this->orderPreviewSplittingFacade->createSplitOrderPreviewForCurrentCustomer($orderData);

        return $this->render('Front/Content/Order/preview.html.twig', [
            'showProducts' => $showProducts,
            'splitOrderPreview' => $splitOrderPreview,
            'showAdvert' => $showAdvert,
        ]);
    }

    /**
     * @param \App\Model\Order\FrontOrderData $frontOrderData
     * @param \App\Model\Order\Preview\SplitOrderPreview $splitOrderPreview
     */
    private function checkTransportAndPaymentChanges(
        FrontOrderData $frontOrderData,
        SplitOrderPreview $splitOrderPreview
    ): void {
        $transportAndPaymentCheckResult = $this->splitTransportAndPaymentWatcher->checkTransportsAndPaymentBySplitOrderPreview(
            $frontOrderData,
            $splitOrderPreview
        );

        if ($transportAndPaymentCheckResult->isTransportPriceChanged()) {
            $this->addInfoFlash(
                t('V průběhu objednávkového procesu byla změněna cena dopravy. Prosím, překontrolujte si objednávku.')
            );
        }
        if ($transportAndPaymentCheckResult->isPaymentPriceChanged()) {
            $this->addInfoFlash(
                t('V průběhu objednávkového procesu byla změněna cena platby. Prosím, překontrolujte si objednávku.')
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
