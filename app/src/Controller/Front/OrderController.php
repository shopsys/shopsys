<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Order\DomainAwareOrderFlowFactory;
use App\Form\Front\Order\OrderFlow;
use App\Form\Front\Order\PaymentFormType;
use App\Model\Cart\CartMigrationFacade;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserFacade;
use App\Model\GoPay\BankSwift\GoPayBankSwift;
use App\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use App\Model\GoPay\Exception\GoPayException;
use App\Model\GoPay\Exception\GoPayNotConfiguredException;
use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use App\Model\GoPay\GoPayOnCurrentDomainFacade;
use App\Model\GoPay\GoPayTransactionFacade;
use App\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use App\Model\Gtm\GtmFacade;
use App\Model\Order\FrontOrderData;
use App\Model\Order\Order;
use App\Model\Order\OrderData;
use App\Model\Order\OrderDataMapper;
use App\Model\Order\Preview\OrderPreview;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Payment\Payment;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Store\StoreFacade;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailException;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentWatcher;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderController extends FrontBaseController
{
    public const SESSION_CREATED_ORDER = 'created_order_id';
    public const SESSION_GOPAY_CHOOSEN_SWIFT = 'gopay_choosen_swift';

    /**
     * @var \App\Form\Front\Order\DomainAwareOrderFlowFactory
     */
    private $domainAwareOrderFlowFactory;

    /**
     * @var \App\Model\Cart\CartFacade
     */
    private $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Order\Mail\OrderMailFacade
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
     * @var \App\Model\Order\Preview\OrderPreviewFactory
     */
    private $orderPreviewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentWatcher
     */
    private $transportAndPaymentWatcher;

    /**
     * @var \App\Model\Payment\PaymentFacade
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
     * @var \App\Model\Transport\TransportFacade
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
     * @var \App\Model\LegalConditions\LegalConditionsFacade
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
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

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
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private ProductAvailabilityFacade $productAvailabilityFacade;

    /**
     * @var \App\Model\Cart\CartMigrationFacade
     */
    private CartMigrationFacade $cartMigrationFacade;

    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade
     */
    private NewsletterFacade $newsletterFacade;

    /**
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Order\OrderDataMapper $orderDataMapper
     * @param \App\Form\Front\Order\DomainAwareOrderFlowFactory $domainAwareOrderFlowFactory
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentWatcher $transportAndPaymentWatcher
     * @param \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \App\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftFacade $goPayBankSwiftFacade
     * @param \App\Model\GoPay\GoPayOnCurrentDomainFacade $goPayFacadeOnCurrentDomain
     * @param \App\Model\GoPay\GoPayTransactionFacade $goPayTransactionFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \App\Model\Gtm\GtmFacade $gtmFacade
     * @param \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Cart\CartMigrationFacade $cartMigrationFacade
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
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
        GoPayBankSwiftFacade $goPayBankSwiftFacade,
        GoPayOnCurrentDomainFacade $goPayFacadeOnCurrentDomain,
        GoPayTransactionFacade $goPayTransactionFacade,
        NewsletterFacade $newsletterFacade,
        StoreFacade $storeFacade,
        GtmFacade $gtmFacade,
        Authenticator $authenticator,
        ProductAvailabilityFacade $productAvailabilityFacade,
        CartMigrationFacade $cartMigrationFacade,
        CustomerUserFacade $customerUserFacade
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
        $this->goPayBankSwiftFacade = $goPayBankSwiftFacade;
        $this->goPayFacadeOnCurrentDomain = $goPayFacadeOnCurrentDomain;
        $this->storeFacade = $storeFacade;
        $this->goPayTransactionFacade = $goPayTransactionFacade;
        $this->newsletterFacade = $newsletterFacade;
        $this->gtmFacade = $gtmFacade;
        $this->authenticator = $authenticator;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->cartMigrationFacade = $cartMigrationFacade;
        $this->customerUserFacade = $customerUserFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request): Response
    {
        $cart = $this->cartFacade->findCartOfCurrentCustomerUser();
        if ($cart === null) {
            return $this->redirectToRoute('front_cart');
        }

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
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
        $goPayBankSwifts = $this->goPayBankSwiftFacade->getAllByCurrencyId($currency->getId());

        $orderFlow = $this->domainAwareOrderFlowFactory->create();

        if ($orderFlow->isBackToCartTransition()) {
            return $this->redirectToRoute('front_cart');
        }

        if ($orderFlow->isBackToTransportAndPaymentTransition()) {
            return $this->redirectToRoute(OrderFlow::ROUTE_NAME_STEP_TRANSPORT_PAYMENT);
        }

        $this->moveOrderFlowByCurrentRequest($request, $orderFlow);

        $orderFlow->bind($frontOrderFormData);
        $orderFlow->saveSentStepData();

        if ($this->session->get(LoginController::SESSION_LOGIN_IN_ORDER_SUCCESS, null) === true) {
            $orderFlow->nextStep();
        }
        $this->session->remove(LoginController::SESSION_LOGIN_IN_ORDER_SUCCESS);
        $form = $orderFlow->createForm();

        $payment = $frontOrderFormData->payment;
        $transport = $frontOrderFormData->transport;
        $personalPickupStore = $frontOrderFormData->personalPickupStore;

        $orderPreview = $this->orderPreviewFactory->createForCurrentUser($transport, $payment);

        $isValid = $orderFlow->isValid($form);
        if ($customerUser instanceof CustomerUser) {
            // Overriding of email, because form field is disabled and data in session could be overridden in another old browser tab
            $frontOrderFormData->email = $customerUser->getEmail();
        }

        /** @var \App\Model\Payment\Payment[] $payments */
        $payments = $this->paymentFacade->getVisibleOnCurrentDomain();

        $transports = $this->transportFacade->getVisibleOnCurrentDomainAcceptingWeight($payments, $cart->getTotalWeight());

        $frontOrderFormData = $this->orderFacade->revalidatePaymentAndTransport($frontOrderFormData, $payments, $transports);
        $orderData = $this->orderDataMapper->getOrderDataFromFrontOrderData($frontOrderFormData);

        $isCompanyCustomer = $frontOrderFormData->companyCustomer;

        $storesById = $this->storeFacade->getStoresEnabledOnDomainIndexedByStoreId($domainId);

        $this->checkTransportAndPaymentChanges($orderData, $orderPreview, $transports, $payments);

        if ($orderFlow->getCurrentStep() === 3 && !$this->isTransportAndPaymentFilledInOrder($orderData, $orderPreview)) {
            return $this->redirectToRoute(OrderFlow::ROUTE_NAME_STEP_TRANSPORT_PAYMENT);
        }

        if ($isValid) {
            if ($orderFlow->nextStep()) {
                $form = $orderFlow->createForm();
            } elseif (count($this->getErrorMessages()) === 0 && count($this->getInfoMessages()) === 0) {
                $deliveryAddress = null;
                if ($orderData->deliveryAddressSameAsBillingAddress === false && $orderData->personalPickupStore === null) {
                    $deliveryAddress = $frontOrderFormData->deliveryAddress;
                }
                $orderCreatedResult = $this->orderFacade->createOrderFromFrontWithRegistration($orderData, $deliveryAddress, $frontOrderFormData);
                $order = $orderCreatedResult->getOrder();

                $this->orderFacade->sendHeurekaOrderInfo($order, $frontOrderFormData->disallowHeurekaVerifiedByCustomers);

                if ($frontOrderFormData->newsletterSubscription) {
                    $this->newsletterFacade->addSubscribedEmail($frontOrderFormData->email, $this->domain->getId());
                }

                if ($order->getCustomerUser() instanceof CustomerUser && $orderCreatedResult->isLoginCustomer()) {
                    $this->authenticator->loginUser($order->getCustomerUser(), $orderFlow->getRequest());
                }

                $this->setGoPayBankSwiftSession($frontOrderFormData->payment, $frontOrderFormData->goPayBankSwift);

                $orderFlow->reset();

                $this->session->set(self::SESSION_CREATED_ORDER, $order->getId());

                try {
                    $this->sendMail($order);
                } catch (MailException $e) {
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

        $this->gtmFacade->onOrderPages($orderPreview, $orderFlow->getCurrentStepNumber());

        if (
            $orderFlow->getCurrentStepNumber() === OrderFlow::STEP_THIRD
            && $this->isGranted(Roles::ROLE_LOGGED_CUSTOMER)
            && $this->cartMigrationFacade->isCartChanged()
        ) {
            $this->addInfoFlash(t('Obsah vašeho košíku se změnil'));
            return $this->redirectToRoute('front_cart');
        }

        $minimalDaysAvailabilityIndexedByTransportIds = [];
        $storeDayAvailabilitiesByStoreId = [];
        if ($orderFlow->getCurrentStepNumber() === OrderFlow::STEP_SECOND) {
            $stores = $this->storeFacade->getStoresEnabledOnDomainIndexedByStoreId($domainId);
            $storeDayAvailabilitiesByStoreId = $this->productAvailabilityFacade->getStoreDayAvailabilitiesIndexedByStoreId(
                $domainId,
                $stores,
                $cart->getQuantifiedProducts()
            );
            $minimalDaysAvailabilityIndexedByTransportIds = $this->productAvailabilityFacade->getMinimalDaysAvailabilityIndexedByTransportIds(
                $domainId,
                $stores,
                $cart->getQuantifiedProducts(),
                $transports
            );
        }

        $customerInfo = $this->customerUserFacade->getCustomerInfo($frontOrderFormData->email, $this->domain->getId());

        return $this->render('Front/Content/Order/index.html.twig', [
            'form' => $form->createView(),
            'flow' => $orderFlow,
            'transport' => $transport,
            'payment' => $payment,
            'personalPickupStore' => $personalPickupStore,
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
            'transports' => $transports,
            'storesById' => $storesById,
            'termsAndConditionsArticle' => $this->legalConditionsFacade->findTermsAndConditions($this->domain->getId()),
            'privacyPolicyArticle' => $this->legalConditionsFacade->findPrivacyPolicy($this->domain->getId()),
            'goPayBankSwifts' => $goPayBankSwifts,
            'goPayBankTransferIdentifier' => GoPayPaymentMethod::IDENTIFIER_BANK_TRANSFER,
            'paymentTransportRelations' => $this->getPaymentTransportRelations($payments),
            'isCompanyCustomer' => $isCompanyCustomer,
            'minimalDaysAvailabilityIndexedByTransportIds' => $minimalDaysAvailabilityIndexedByTransportIds,
            'storeDayAvailabilitiesByStoreId' => $storeDayAvailabilitiesByStoreId,
            'customerInfo' => $customerInfo,
            'customerUser' => $customerUser,
            'orderPreview' => $orderPreview,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Form\Front\Order\OrderFlow $orderFlow
     */
    private function moveOrderFlowByCurrentRequest(Request $request, OrderFlow $orderFlow): void
    {
        $routeName = $request->get('_route');
        if ($routeName === OrderFlow::ROUTE_NAME_STEP_PERSONAL_INFO) {
            $orderFlow->setStep(3);
        }
        if ($routeName === OrderFlow::ROUTE_NAME_STEP_TRANSPORT_PAYMENT) {
            $orderFlow->setStep(2);
        }
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Order\Preview\OrderPreview $orderPreview
     * @return bool
     */
    private function isTransportAndPaymentFilledInOrder(OrderData $orderData, OrderPreview $orderPreview): bool
    {
        if ($orderData->transport === null || $orderPreview->getTransport() === null) {
            $this->addErrorFlashTwig(t('Shipping is not filled in the order. Please check your order.'));
            return false;
        }

        if ($orderData->payment === null || $orderPreview->getPayment() === null) {
            $this->addErrorFlashTwig(t('The payment method is not filled in the order. Please check your order.'));
            return false;
        }

        return true;
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
        $transportId = $request->get('transportId');
        $paymentId = $request->get('paymentId');
        $showProducts = $request->get('showProducts', true);
        $showAdvert = $request->get('showAdvert', true);
        $showPromo = $request->get('showPromo', true);
        $orderStep = $request->get('orderStep');

        if ($transportId === null) {
            $transport = null;
        } else {
            /** @var \App\Model\Transport\Transport $transport */
            $transport = $this->transportFacade->getById($transportId);
        }

        if ($paymentId === null) {
            $payment = null;
        } else {
            $payment = $this->paymentFacade->getById($paymentId);
        }

        $orderPreview = $this->orderPreviewFactory->createForCurrentUser($transport, $payment);

        return $this->render('Front/Content/Order/preview.html.twig', [
            'showProducts' => $showProducts,
            'orderPreview' => $orderPreview,
            'showAdvert' => $showAdvert,
            'showPromo' => $showPromo,
            'orderStep' => $orderStep,
        ]);
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Order\Preview\OrderPreview $orderPreview
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
            } catch (GoPayException $e) {
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
        } catch (OrderNotFoundException $e) {
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
        } catch (OrderNotFoundException $e) {
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
        }

        return $this->render('Front/Content/Order/notPaid.html.twig', [
            'goPayBankTransferIdentifier' => GoPayPaymentMethod::IDENTIFIER_BANK_TRANSFER,
            'urlHash' => $urlHash,
            'order' => $order,
        ]);
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
        } catch (OrderNotFoundException $e) {
            $this->addErrorFlash(t('Objednávka nebyla nalezena.'));

            return $this->redirectToRoute('front_homepage');
        }

        $goPayData = null;

        if (!$order->getPayment()->isGoPay()) {
            throw $this->createNotFoundException('Objednávka nemá nastaven způsob platby prostřednictvím GoPay.');
        }

        if ($order->isGoPayPaid()) {
            $this->addErrorFlash(t('Objednávka je již zaplacená.'));
            return $this->redirectToRoute('front_homepage');
        }

        $goPayBankSwift = $this->session->get(self::SESSION_GOPAY_CHOOSEN_SWIFT, null);

        try {
            $goPayData = $this->goPayFacadeOnCurrentDomain->sendPaymentToGoPay($order, $goPayBankSwift);
            $this->goPayTransactionFacade->createNewTransactionByOrder($order, (string)$goPayData['goPayId']);
        } catch (GoPayException $e) {
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
        }

        return $this->redirectToRoute('front_customer_order_detail_unregistered', [
            'urlHash' => $order->getUrlHash(),
        ]);
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
}
