<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Login\LoginFormType;
use App\Form\Front\Order\DomainAwareOrderFlowFactory;
use App\Form\Front\Order\PaymentFormType;
use App\Model\GoPay\BankSwift\GoPayBankSwift;
use App\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use App\Model\GoPay\Exception\GoPayNotConfiguredException;
use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use App\Model\GoPay\GoPayOnCurrentDomainFacade;
use App\Model\GoPay\GoPayTransactionFacade;
use App\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use App\Model\Gtm\GtmFacade;
use App\Model\Order\FrontOrderData;
use App\Model\Order\Order;
use App\Model\Order\OrderDataFactory;
use App\Model\Order\OrderDataMapper;
use App\Model\Order\Preview\OrderPreviewSplittingFacade;
use App\Model\Order\Preview\SplitOrderPreview;
use App\Model\Order\Watcher\SplitTransportAndPaymentWatcher;
use App\Model\Payment\Payment;
use App\Model\Stock\StockFacade;
use App\Model\Transport\Logistic\TransportLogisticFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderController extends FrontBaseController
{
    public const SESSION_CREATED_ORDER = 'created_order_id';
    public const SESSION_GOPAY_CHOOSEN_SWIFT = 'gopay_choosen_swift';
    public const SESSION_CUSTOMER_EMAIL_EXISTS = 'customer_email_exists';
    public const SESSION_PREFILLED_CUSTOMER_EMAIL = 'prefilled_customer_email';

    /**
     * @var \App\Form\Front\Order\DomainAwareOrderFlowFactory
     */
    private $domainAwareOrderFlowFactory;

    /**
     * @var \App\Model\Cart\CartFacade
     */
    private $cartFacade;

    /**
     * @var \App\Component\Domain\Domain
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
     * @var \App\Model\Payment\PaymentFacade
     */
    private $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \App\Model\Transport\TransportFacade
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
     * @var \App\Model\GoPay\BankSwift\GoPayBankSwiftFacade
     */
    private $goPayBankSwiftFacade;

    /**
     * @var \App\Model\GoPay\GoPayOnCurrentDomainFacade
     */
    private $goPayFacadeOnCurrentDomain;

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
     * @var \App\Model\GoPay\GoPayTransactionFacade
     */
    private $goPayTransactionFacade;

    /**
     * @var \App\Model\Gtm\GtmFacade
     */
    private $gtmFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\Authenticator
     */
    private $authenticator;

    /**
     * @var \App\Model\Transport\Logistic\TransportLogisticFacade
     */
    private TransportLogisticFacade $transportLogisticFacade;

    /**
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Order\OrderDataMapper $orderDataMapper
     * @param \App\Form\Front\Order\DomainAwareOrderFlowFactory $domainAwareOrderFlowFactory
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \App\Model\Order\Watcher\SplitTransportAndPaymentWatcher $splitTransportAndPaymentWatcher
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftFacade $goPayBankSwiftFacade
     * @param \App\Model\GoPay\GoPayOnCurrentDomainFacade $goPayFacadeOnCurrentDomain
     * @param \App\Model\GoPay\GoPayTransactionFacade $goPayTransactionFacade
     * @param \App\Model\Order\Preview\OrderPreviewSplittingFacade $orderPreviewSplittingFacade
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\Gtm\GtmFacade $gtmFacade
     * @param \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator
     * @param \App\Model\Transport\Logistic\TransportLogisticFacade $transportLogisticFacade
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
        GoPayBankSwiftFacade $goPayBankSwiftFacade,
        GoPayOnCurrentDomainFacade $goPayFacadeOnCurrentDomain,
        GoPayTransactionFacade $goPayTransactionFacade,
        OrderPreviewSplittingFacade $orderPreviewSplittingFacade,
        OrderDataFactory $orderDataFactory,
        StockFacade $stockFacade,
        GtmFacade $gtmFacade,
        Authenticator $authenticator,
        TransportLogisticFacade $transportLogisticFacade
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
        $this->goPayBankSwiftFacade = $goPayBankSwiftFacade;
        $this->goPayFacadeOnCurrentDomain = $goPayFacadeOnCurrentDomain;
        $this->orderPreviewSplittingFacade = $orderPreviewSplittingFacade;
        $this->orderDataFactory = $orderDataFactory;
        $this->stockFacade = $stockFacade;
        $this->goPayTransactionFacade = $goPayTransactionFacade;
        $this->gtmFacade = $gtmFacade;
        $this->authenticator = $authenticator;
        $this->transportLogisticFacade = $transportLogisticFacade;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        $cart = $this->cartFacade->findCartOfCurrentCustomerUser();
        if ($cart === null) {
            return $this->redirectToRoute('front_cart');
        }

        $customerUser = $this->getUser();
        $frontOrderFormData = new FrontOrderData();
        $frontOrderFormData->deliveryAddressSameAsBillingAddress = true;
        $isCompanyCustomer = false;
        $isWithoutRegistration = false;
        if ($customerUser instanceof CustomerUser) {
            $this->orderFacade->prefillFrontOrderData($frontOrderFormData, $customerUser);
            $isCompanyCustomer = $customerUser->getCustomer()->getBillingAddress()->isCompanyCustomer();
            $isWithoutRegistration = true;
        }
        $domainId = $this->domain->getId();
        $frontOrderFormData->domainId = $domainId;
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $frontOrderFormData->currency = $currency;
        $goPayBankSwifts = $this->goPayBankSwiftFacade->getAllByCurrencyId($currency->getId());

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

        if ($this->session->get(LoginController::SESSION_LOGIN_IN_ORDER_SUCCESS, null) === true) {
            $orderFlow->nextStep();
            $this->session->remove(LoginController::SESSION_LOGIN_IN_ORDER_SUCCESS);
        }
        $form = $orderFlow->createForm();
        $isValid = $orderFlow->isValid($form);

        $payments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $payments = $this->paymentFacade->filterAllowedPaymentsForCurrentCart($payments);
        $transports = $this->transportFacade->getVisibleOnCurrentDomain($payments);
        $transports = $this->transportLogisticFacade->filterAllowedTransportsForCurrentCart($transports);

        $frontOrderFormData = $this->orderFacade->revalidatePaymentAndTransport($frontOrderFormData, $payments, $transports);
        $orderData = $this->orderDataMapper->getOrderDataFromFrontOrderData($frontOrderFormData);
        $splitOrderPreview = $this->orderPreviewSplittingFacade->createSplitOrderPreviewForCurrentCustomer($orderData);

        $stocksById = $this->stockFacade->getStocksWithoutCentralByDomainIdIndexedByStockId($domainId);
        $prefilledCustomerEmail = $this->session->get(self::SESSION_PREFILLED_CUSTOMER_EMAIL, null);

        $this->checkTransportAndPaymentChanges($frontOrderFormData, $splitOrderPreview);
        if ($isValid) {
            if ($orderFlow->nextStep()) {
                $form = $orderFlow->createForm();
            } elseif ($splitOrderPreview->areAllTransportsSet() === false) {
                $this->addInfoFlash(
                    t('Došlo ke změně v košíku, která vyžaduje, aby jste překontrolovali dopravu objednávky.')
                );
                return $this->redirectToRoute('front_order_index');
            } elseif (count($this->getErrorMessages()) === 0 && count($this->getInfoMessages()) === 0) {
                $deliveryAddress = $orderData->deliveryAddressSameAsBillingAddress === false ? $frontOrderFormData->deliveryAddress : null;
                $order = $this->orderFacade->createOrderFromFront($orderData, $deliveryAddress);
                $this->orderFacade->sendHeurekaOrderInfo($order, $frontOrderFormData->disallowHeurekaVerifiedByCustomers);

                if ($isWithoutRegistration === false && $order->getCustomerUser() instanceof CustomerUser) {
                    $this->authenticator->loginUser($order->getCustomerUser(), $orderFlow->getRequest());
                }

                $this->setGoPayBankSwiftSession($frontOrderFormData->payment, $frontOrderFormData->goPayBankSwift);

                $orderFlow->reset();

                $this->session->set(self::SESSION_CREATED_ORDER, $order->getId());

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

        $this->gtmFacade->onOrderPages($splitOrderPreview, $orderFlow->getCurrentStepNumber());

        if ($isValid && $orderFlow->getCurrentStepNumber() === 3 && $this->isGranted(Roles::ROLE_LOGGED_CUSTOMER) === false) {
            $customerEmailExists = $this->session->get(self::SESSION_CUSTOMER_EMAIL_EXISTS, null);
            $this->session->remove(self::SESSION_CUSTOMER_EMAIL_EXISTS);
            if ($customerEmailExists !== false) {
                return $this->render('Front/Content/Order/index.html.twig', [
                    'form' => $this->getLoginForm()->createView(),
                    'flow' => $orderFlow,
                    'displayFormType' => 'login',
                    'customerEmailExists' => $customerEmailExists,
                    'loginFormInOrder' => true,
                    'splitOrderPreview' => $splitOrderPreview,
                    'prefilledCustomerEmail' => $prefilledCustomerEmail,
                ]);
            }
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
            'goPayBankSwifts' => $goPayBankSwifts,
            'goPayBankTransferIdentifier' => GoPayPaymentMethod::IDENTIFIER_BANK_TRANSFER,
            'paymentTransportRelations' => $this->getPaymentTransportRelations($payments),
            'isWithoutRegistration' => $isWithoutRegistration,
            'isCompanyCustomer' => $isCompanyCustomer,
            'displayFormType' => 'order_flow',
            'prefilledCustomerEmail' => ($this->isGranted(Roles::ROLE_LOGGED_CUSTOMER) === false) ? $prefilledCustomerEmail : null,
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function previewAction(Request $request): Response
    {
        $transportIdsByProductTypeId = $request->get('transportIdsByProductTypeId');
        $paymentId = $request->get('paymentId');
        $showProducts = $request->get('showProducts', true);
        $showAdvert = $request->get('showAdvert', true);
        $showPromo = $request->get('showPromo', true);

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
            'showPromo' => $showPromo,
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

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveOrderFormAction(): Response
    {
        $flow = $this->domainAwareOrderFlowFactory->create();
        $flow->bind(new FrontOrderData());
        $form = $flow->createForm();
        $flow->saveCurrentStepData($form);

        return new Response();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function sentAction(): Response
    {
        $orderId = $this->session->get(self::SESSION_CREATED_ORDER, null);
        $this->session->remove(self::SESSION_CREATED_ORDER);

        if ($orderId === null) {
            return $this->redirectToRoute('front_cart');
        }

        /** @var \App\Model\Order\Order $order */
        $order = $this->orderFacade->getById($orderId);
        $goPayData = null;

        $this->gtmFacade->onOrderSentPage($order);

        if ($order->getPayment()->isGoPay()) {
            $goPayBankSwift = $this->session->get(self::SESSION_GOPAY_CHOOSEN_SWIFT, null);

            try {
                $goPayData = $this->goPayFacadeOnCurrentDomain->sendPaymentToGoPay($order, $goPayBankSwift);
                $this->goPayTransactionFacade->createNewTransactionByOrder($order, (string)$goPayData['goPayId']);
            } catch (\App\Model\GoPay\Exception\GoPayException $e) {
                $this->addErrorFlash(t('Connection to GoPay gateway failed.'));
            }
        }

        $this->session->remove(self::SESSION_GOPAY_CHOOSEN_SWIFT);

        return $this->render('Front/Content/Order/sent.html.twig', [
            'pageContent' => $this->orderFacade->getOrderSentPageContent($orderId),
            'order' => $order,
            'goPayData' => $goPayData,
        ]);
    }

    /**
     * @param string $urlHash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function paidAction(string $urlHash): Response
    {
        try {
            /** @var \App\Model\Order\Order $order */
            $order = $this->orderFacade->getByUrlHashAndDomain($urlHash, $this->domain->getId());
        } catch (\Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException $e) {
            $this->addErrorFlash(t('Order not found.'));
            return $this->redirectToRoute('front_cart');
        }

        if ($order->getPayment()->isGoPay()) {
            $this->checkOrderGoPayStatus($order);
            if ($this->goPayFacadeOnCurrentDomain->isOrderGoPayUnpaid($order)) {
                return $this->redirectToRoute('front_order_not_paid', ['urlHash' => $urlHash]);
            }
        }

        return $this->render('Front/Content/Order/sent.html.twig', [
            'pageContent' => $this->orderFacade->getOrderSentPageContent($order->getId()),
            'order' => $order,
        ]);
    }

    /**
     * @param string $urlHash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function notPaidAction(string $urlHash): Response
    {
        try {
            $order = $this->orderFacade->getByUrlHashAndDomain($urlHash, $this->domain->getId());
        } catch (\Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException $e) {
            $this->addErrorFlash(t('Order not found.'));

            return $this->redirectToRoute('front_cart');
        }

        if (!$order->getPayment()->isGoPay()) {
            return $this->redirectToRoute('front_cart');
        }

        $this->gtmFacade->onOrderNotPaidPage($order);

        if ($this->orderFacade->isUnpaidOrderPaymentChangeable($order)) {
            $payments = $this->paymentFacade->getVisibleOnCurrentDomainByTransport($order->getTransport());
            $goPayBankSwifts = $this->goPayBankSwiftFacade->getAllByCurrencyId($order->getCurrency()->getId());

            $form = $this->createForm(PaymentFormType::class, [], [
                'action' => $this->generateUrl('front_order_change_payment_method', ['urlHash' => $order->getUrlHash()]),
                'method' => 'POST',
                'payments' => $payments,
                'goPayBankSwifts' => $goPayBankSwifts,
            ]);

            return $this->render('Front/Content/Order/changePayment.html.twig', [
                'form' => $form->createView(),
                'goPayBankTransferIdentifier' => GoPayPaymentMethod::IDENTIFIER_BANK_TRANSFER,
                'payments' => $payments,
                'urlHash' => $urlHash,
                'unsuccessfulPayment' => $order->getPayment(),
                'order' => $order,
            ]);
        } else {
            return $this->render('Front/Content/Order/notPaid.html.twig', [
                'goPayBankTransferIdentifier' => GoPayPaymentMethod::IDENTIFIER_BANK_TRANSFER,
                'urlHash' => $urlHash,
                'order' => $order,
            ]);
        }
    }

    /**
     * @param \App\Model\Order\Order $order
     */
    private function checkOrderGoPayStatus(Order $order): void
    {
        try {
            $this->goPayTransactionFacade->updateOrderTransactions($order);
        } catch (GoPayNotConfiguredException | GoPayPaymentDownloadException $e) {
            $this->addErrorFlash(t('Connection to GoPay gateway failed.'));
        }
    }

    /**
     * @param string $urlHash
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function repeatGoPayPaymentAction(string $urlHash): Response
    {
        try {
            /** @var \App\Model\Order\Order $order */
            $order = $this->orderFacade->getByUrlHashAndDomain($urlHash, $this->domain->getId());
        } catch (\Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException $e) {
            $this->addErrorFlash(t('Objednávka nebyla nalezena.'));

            return $this->redirectToRoute('front_homepage');
        }

        $goPayData = null;

        if ($order->getPayment()->isGoPay()) {
            if ($order->isGoPayPaid()) {
                $this->addErrorFlash(t('Objednávka je již zaplacená.'));
                return $this->redirectToRoute('front_homepage');
            }
        } else {
            throw $this->createNotFoundException('Objednávka nemá nastaven způsob platby prostřednictvím GoPay.');
        }

        $goPayBankSwift = $this->session->get(self::SESSION_GOPAY_CHOOSEN_SWIFT, null);

        try {
            $goPayData = $this->goPayFacadeOnCurrentDomain->sendPaymentToGoPay($order, $goPayBankSwift);
            $this->goPayTransactionFacade->createNewTransactionByOrder($order, (string)$goPayData['goPayId']);
        } catch (\App\Model\GoPay\Exception\GoPayException $e) {
            $this->addErrorFlash(t('Connection to GoPay gateway failed.'));
        }

        return $this->render('Front/Content/Order/repeatGoPayPayment.html.twig', [
            'order' => $order,
            'goPayData' => $goPayData,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $urlHash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changePaymentAction(Request $request, string $urlHash): RedirectResponse
    {
        try {
            $order = $this->orderFacade->getByUrlHashAndDomain($urlHash, $this->domain->getId());
        } catch (OrderNotFoundException $e) {
            $this->addErrorFlash(t('Objednávka nebyla nalezena.'));
            return $this->redirectToRoute('front_homepage');
        }

        $payments = $this->paymentFacade->getVisibleOnCurrentDomainByTransport($order->getTransport());

        $goPayBankSwifts = $this->goPayBankSwiftFacade->getAllByCurrencyId($order->getCurrency()->getId());

        $form = $this->createForm(PaymentFormType::class, [], [
            'payments' => $payments,
            'goPayBankSwifts' => $goPayBankSwifts,
        ]);

        $form->handleRequest($request);

        /** @var \App\Model\Payment\Payment $selectedPayment */
        $selectedPayment = $form['payment']->getData();
        $selectedGoPayPaymentSwift = $form['goPayBankSwift']->getData();
        $this->setGoPayBankSwiftSession($selectedPayment, $selectedGoPayPaymentSwift);

        $this->orderFacade->changeOrderPayment($order, $selectedPayment, $this->domain->getId());

        $this->session->set(self::SESSION_CREATED_ORDER, $order->getId());

        $this->addInfoFlash(t('Způsob platby byl úspěšně změněn'));

        if ($this->getUser() instanceof CustomerUser) {
            return $this->redirectToRoute('front_customer_order_detail_registered', [
                'orderNumber' => $order->getNumber(),
            ]);
        } else {
            return $this->redirectToRoute('front_customer_order_detail_unregistered', [
                'urlHash' => $order->getUrlHash(),
            ]);
        }
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwift|null $goPayBankSwift
     */
    private function setGoPayBankSwiftSession(Payment $payment, ?GoPayBankSwift $goPayBankSwift): void
    {
        if ($payment->isGoPay()) {
            if ($goPayBankSwift !== null) {
                $goPayBankSwiftCode = $goPayBankSwift->getSwift();
            } else {
                $goPayBankSwiftCode = null;
            }

            $this->session->set(self::SESSION_GOPAY_CHOOSEN_SWIFT, $goPayBankSwiftCode);
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function termsAndConditionsAction(): Response
    {
        return $this->getTermsAndConditionsResponse();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse
     */
    public function termsAndConditionsDownloadAction(): Response
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
    private function getTermsAndConditionsResponse(): Response
    {
        return $this->render('Front/Content/Order/legalConditions.html.twig', [
            'termsAndConditionsArticle' => $this->legalConditionsFacade->findTermsAndConditions($this->domain->getId()),
        ]);
    }

    /**
     * @param \App\Model\Order\Order $order
     */
    private function sendMail($order): void
    {
        $mailTemplate = $this->orderMailFacade->getMailTemplateByStatusAndDomainId($order->getStatus(), $order->getDomainId());
        if ($mailTemplate->isSendMail()) {
            $this->orderMailFacade->sendEmail($order);
        }
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getLoginForm()
    {
        return $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('front_login_check'),
        ]);
    }
}
