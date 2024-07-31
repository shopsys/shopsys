<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Order;
use App\Model\Order\OrderData;
use App\Model\Order\Status\OrderStatus;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class CompanyOrderDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = '0338e624-c961-4475-a29d-c90080d02d1f';
    public const string ORDER_PREFIX = 'order_';

    /**
     * @param \App\Model\Order\PlaceOrderFacade $placeOrderFacade
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory
     */
    public function __construct(
        private readonly PlaceOrderFacade $placeOrderFacade,
        private readonly OrderDataFactory $orderDataFactory,
        private readonly Domain $domain,
        private readonly CurrencyFacade $currencyFacade,
        private readonly OrderProcessor $orderProcessor,
        private readonly OrderInputFactory $orderInputFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            if (!$domainConfig->isB2b()) {
                continue;
            }
            $this->loadOrders($domainConfig->getId());
        }
    }

    /**
     * @param int $domainId
     */
    private function loadOrders(int $domainId): void
    {
        $companyCustomer = $this->getReferenceForDomain(CompanyDataFixture::SHOPSYS_COMPANY, $domainId, Customer::class);
        $domainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $orderData = $this->orderDataFactory->create();
        $customerUser = $this->getReferenceForDomain(CompanyDataFixture::COMPANY_USER_JOZEF_NOVOTNY, $domainId, CustomerUser::class);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE, OrderStatus::class);
        $this->mapCustomerUserDataToOrderData($orderData, $customerUser);
        $this->mapCompanyAddressDataToOrderData($orderData, $companyCustomer);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -3 day'))->setTime(12, 40, 22);

        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '9' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 3,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_GOPAY_DOMAIN . $domainId,
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $this->mapCustomerUserDataToOrderData($orderData, $customerUser);
        $this->mapCompanyAddressDataToOrderData($orderData, $companyCustomer);
        $this->mapDeliveryAddressDataToOrderData($orderData, $customerUser);
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -11 day'))->setTime(4, 34, 19);
        $orderData->promoCode = 'promoCode123';
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '18' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '19' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '20' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '15' => 5,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_CARD,
            $customerUser,
        );

        $customerUser = $this->getReferenceForDomain(CompanyDataFixture::COMPANY_USER_MAREK_HORVATH, $domainId, CustomerUser::class);
        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $this->mapCustomerUserDataToOrderData($orderData, $customerUser);
        $this->mapCompanyAddressDataToOrderData($orderData, $companyCustomer);
        $this->mapDeliveryAddressDataToOrderData($orderData, $customerUser);
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -3 day'))->setTime(18, 27, 36);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '4' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '11' => 1,
            ],
            TransportDataFixture::TRANSPORT_CZECH_POST,
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $this->mapCustomerUserDataToOrderData($orderData, $customerUser);
        $this->mapCompanyAddressDataToOrderData($orderData, $companyCustomer);
        $this->mapDeliveryAddressDataToOrderData($orderData, $customerUser);
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -1 day'))->setTime(18, 30, 01);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '1' => 1,
            ],
            TransportDataFixture::TRANSPORT_PPL,
            PaymentDataFixture::PAYMENT_CARD,
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $this->mapCustomerUserDataToOrderData($orderData, $customerUser);
        $this->mapCompanyAddressDataToOrderData($orderData, $companyCustomer);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -2 day'))->setTime(1, 46, 6);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '2' => 8,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '1' => 2,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_CASH,
            $customerUser,
        );
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param array<string, int> $products
     * @param string $transportReferenceName
     * @param string $paymentReferenceName
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @return \App\Model\Order\Order
     */
    private function createOrder(
        OrderData $orderData,
        array $products,
        string $transportReferenceName,
        string $paymentReferenceName,
        ?CustomerUser $customerUser = null,
    ): Order {
        $uniqueOrderHash = $orderData->domainId . '-';

        $transport = $this->getReference($transportReferenceName, Transport::class);
        $payment = $this->getReference($paymentReferenceName, Payment::class);

        $orderInput = $this->orderInputFactory->create($this->domain->getDomainConfigById($orderData->domainId));
        $orderInput->setTransport($transport);
        $orderInput->setPayment($payment);
        $orderInput->setCustomerUser($customerUser);

        foreach ($products as $productReferenceName => $quantity) {
            $product = $this->getReference($productReferenceName, Product::class);
            $orderInput->addProduct($product, $quantity);
            $uniqueOrderHash .= $product->getCatnum() . '-' . $quantity;
        }

        $uniqueOrderHash .= $orderData->firstName . $orderData->lastName . $transport->getId() . $orderData->deliveryFirstName . $orderData->deliveryLastName;
        $orderData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, md5($uniqueOrderHash))->toString();

        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );

        $order = $this->placeOrderFacade->createOrderOnly($orderData);

        $referenceName = self::ORDER_PREFIX . $order->getId();
        $this->addReference($referenceName, $order);

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
            TransportDataFixture::class,
            PaymentDataFixture::class,
            OrderStatusDataFixture::class,
            CompanyDataFixture::class,
        ];
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     */
    private function mapCompanyAddressDataToOrderData(OrderData $orderData, Customer $customer): void
    {
        $customerBillingAddress = $customer->getBillingAddress();
        $orderData->street = $customerBillingAddress->getStreet();
        $orderData->city = $customerBillingAddress->getCity();
        $orderData->postcode = $customerBillingAddress->getPostcode();
        $orderData->country = $customerBillingAddress->getCountry();
        $orderData->isCompanyCustomer = true;
        $orderData->companyName = $customerBillingAddress->getCompanyName();
        $orderData->companyNumber = $customerBillingAddress->getCompanyNumber();
        $orderData->companyTaxNumber = $customerBillingAddress->getCompanyTaxNumber();
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    private function mapCustomerUserDataToOrderData(OrderData $orderData, CustomerUser $customerUser): void
    {
        $orderData->firstName = $customerUser->getFirstName();
        $orderData->lastName = $customerUser->getLastName();
        $orderData->email = $customerUser->getEmail();
        $orderData->telephone = $customerUser->getTelephone();
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    private function mapDeliveryAddressDataToOrderData(
        OrderData $orderData,
        CustomerUser $customerUser,
    ): void {
        $defaultDeliveryAddress = $customerUser->getDefaultDeliveryAddress();

        if ($defaultDeliveryAddress === null) {
            $orderData->deliveryAddressSameAsBillingAddress = true;

            return;
        }
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryCity = $defaultDeliveryAddress->getCity();
        $orderData->deliveryStreet = $defaultDeliveryAddress->getStreet();
        $orderData->deliveryPostcode = $defaultDeliveryAddress->getPostcode();
        $orderData->deliveryCountry = $defaultDeliveryAddress->getCountry();
        $orderData->deliveryFirstName = $defaultDeliveryAddress->getFirstName();
        $orderData->deliveryLastName = $defaultDeliveryAddress->getLastName();
        $orderData->deliveryCompanyName = $defaultDeliveryAddress->getCompanyName();
    }
}
