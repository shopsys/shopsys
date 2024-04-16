<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Administrator\Administrator;
use App\Model\Order\Order;
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
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Order\CreateOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\InputOrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class OrderDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = '0338e624-c961-4475-a29d-c90080d02d1f';
    public const string ORDER_PREFIX = 'order_';
    private const string ORDER_WITH_GOPAY_PAYMENT_PREFIX = 'order_with_gopay_payment_';
    public const string ORDER_WITH_GOPAY_PAYMENT_1 = 'order_with_gopay_payment_1';
    public const string ORDER_WITH_GOPAY_PAYMENT_14 = 'order_with_gopay_payment_14';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \App\Model\Order\CreateOrderFacade $createOrderFacade
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\InputOrderDataFactory $inputOrderDataFactory
     */
    public function __construct(
        private readonly CustomerUserRepository $customerUserRepository,
        private readonly CreateOrderFacade $createOrderFacade,
        private readonly OrderDataFactory $orderDataFactory,
        private readonly Domain $domain,
        private readonly CurrencyFacade $currencyFacade,
        private readonly OrderProcessor $orderProcessor,
        private readonly InputOrderDataFactory $inputOrderDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();

            if ($domainId === Domain::SECOND_DOMAIN_ID) {
                $this->loadDistinct($domainId);
            } else {
                $this->loadDefault($domainId);
            }
        }
    }

    /**
     * @param int $domainId
     */
    private function loadDefault(int $domainId): void
    {
        $domainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain(
            'no-reply@shopsys.com',
            $domainId,
        );
        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE, OrderStatus::class);
        $orderData->firstName = 'Jiří';
        $orderData->lastName = 'Ševčík';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420369554147';
        $orderData->street = 'První 1';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71200';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -3 day'))->setTime(12, 40, 22);

        $order = $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '9' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 3,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_GOPAY_DOMAIN . $domainId,
            $customerUser,
        );
        $this->addReference(self::ORDER_WITH_GOPAY_PAYMENT_PREFIX . $order->getId(), $order);

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'Iva';
        $orderData->lastName = 'Jačková';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420367852147';
        $orderData->street = 'Druhá 2';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71300';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -11 day'))->setTime(4, 34, 19);
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR, Administrator::class);
        $orderData->gtmCoupon = 'promoCode123';
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

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'Jan';
        $orderData->lastName = 'Adamovský';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420725852147';
        $orderData->street = 'Třetí 3';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71200';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
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
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS, OrderStatus::class);
        $orderData->trackingNumber = '48976519372';
        $orderData->firstName = 'Iveta';
        $orderData->lastName = 'Prvá';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420606952147';
        $orderData->street = 'Čtvrtá 4';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '70030';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
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
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE, OrderStatus::class);
        $orderData->firstName = 'Jana';
        $orderData->lastName = 'Janíčková';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420739852148';
        $orderData->street = 'Pátá 55';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71200';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -2 day'))->setTime(1, 46, 6);
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
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

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'Dominik';
        $orderData->lastName = 'Hašek';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420721852152';
        $orderData->street = 'Šestá 39';
        $orderData->city = 'Pardubice';
        $orderData->postcode = '58941';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -12 day'))->setTime(0, 49, 0);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '13' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '15' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '16' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '17' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '18' => 1,
            ],
            TransportDataFixture::TRANSPORT_PPL,
            PaymentDataFixture::PAYMENT_CARD,
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_CANCELED, OrderStatus::class);
        $orderData->firstName = 'Jiří';
        $orderData->lastName = 'Sovák';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420755872155';
        $orderData->street = 'Sedmá 1488';
        $orderData->city = 'Opava';
        $orderData->postcode = '85741';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -13 day'))->setTime(23, 35, 15);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '8' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '12' => 2,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_CASH,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE, OrderStatus::class);
        $orderData->firstName = 'Josef';
        $orderData->lastName = 'Somr';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420369852147';
        $orderData->street = 'Osmá 1';
        $orderData->city = 'Praha';
        $orderData->postcode = '30258';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -5 day'))->setTime(9, 11, 59);
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '1' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '2' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '12' => 1,
            ],
            TransportDataFixture::TRANSPORT_CZECH_POST,
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_CANCELED, OrderStatus::class);
        $orderData->firstName = 'Ivan';
        $orderData->lastName = 'Horník';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420755496328';
        $orderData->street = 'Desátá 10';
        $orderData->city = 'Plzeň';
        $orderData->postcode = '30010';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -14 day'))->setTime(12, 54, 07);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '9' => 3,
                ProductDataFixture::PRODUCT_PREFIX . '13' => 2,
            ],
            TransportDataFixture::TRANSPORT_DRONE,
            PaymentDataFixture::PAYMENT_CASH,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->trackingNumber = '1234567890';
        $orderData->firstName = 'Adam';
        $orderData->lastName = 'Bořič';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420987654321';
        $orderData->street = 'Cihelní 5';
        $orderData->city = 'Liberec';
        $orderData->postcode = '65421';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -7 day'))->setTime(7, 2, 31);
        $orderData->gtmCoupon = 'promoCode123';
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ],
            TransportDataFixture::TRANSPORT_PPL,
            PaymentDataFixture::PAYMENT_CARD,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS, OrderStatus::class);
        $orderData->firstName = 'Evžen';
        $orderData->lastName = 'Farný';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420456789123';
        $orderData->street = 'Gagarinova 333';
        $orderData->city = 'Hodonín';
        $orderData->postcode = '69501';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -7 day'))->setTime(11, 28, 20);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '1' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '2' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_CASH,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE, OrderStatus::class);
        $orderData->firstName = 'Ivana';
        $orderData->lastName = 'Janečková';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420369852147';
        $orderData->street = 'Kalužní 88';
        $orderData->city = 'Lednice';
        $orderData->postcode = '69144';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -10 day'))->setTime(18, 3, 36);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '4' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_CASH,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'Pavel';
        $orderData->lastName = 'Novák';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420605123654';
        $orderData->street = 'Adresní 6';
        $orderData->city = 'Opava';
        $orderData->postcode = '72589';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -10 day'))->setTime(23, 47, 11);
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR, Administrator::class);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '10' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '20' => 4,
            ],
            TransportDataFixture::TRANSPORT_CZECH_POST,
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE, OrderStatus::class);
        $orderData->trackingNumber = '48172539041';
        $orderData->firstName = 'Pavla';
        $orderData->lastName = 'Adámková';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+4206051836459';
        $orderData->street = 'Výpočetni 16';
        $orderData->city = 'Praha';
        $orderData->postcode = '30015';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -10 day'))->setTime(8, 14, 8);
        $order = $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '15' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '18' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '19' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_GOPAY_DOMAIN . $domainId,
        );
        $this->addReference(self::ORDER_WITH_GOPAY_PAYMENT_PREFIX . $order->getId(), $order);

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS, OrderStatus::class);
        $orderData->firstName = 'Adam';
        $orderData->lastName = 'Žitný';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+4206051836459';
        $orderData->street = 'Přímá 1';
        $orderData->city = 'Plzeň';
        $orderData->postcode = '30010';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -11 day'))->setTime(4, 43, 25);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '19' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '6' => 1,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_CASH,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'Radim';
        $orderData->lastName = 'Svátek';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420733598748';
        $orderData->street = 'Křivá 11';
        $orderData->city = 'Jablonec';
        $orderData->postcode = '78952';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->companyName = 'BestCompanyEver, s.r.o.';
        $orderData->companyNumber = '555555';
        $orderData->note = 'Doufám, že vše dorazí v pořádku a co nejdříve :)';
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -5 day'))->setTime(3, 3, 12);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '17' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 2,
            ],
            TransportDataFixture::TRANSPORT_PPL,
            PaymentDataFixture::PAYMENT_CARD,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'Radim';
        $orderData->lastName = 'Svátek';
        $orderData->email = 'vitek@shopsys.com';
        $orderData->telephone = '+420733598748';
        $orderData->street = 'Křivá 11';
        $orderData->city = 'Jablonec';
        $orderData->postcode = '78952';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryCity = 'Ostrava';
        $orderData->deliveryCompanyName = 'BestCompanyEver, s.r.o.';
        $orderData->deliveryCountry = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->deliveryStreet = 'Křivá 11';
        $orderData->deliveryTelephone = '+421555444';
        $orderData->deliveryPostcode = '01305';
        $orderData->deliveryFirstName = 'Pavol';
        $orderData->deliveryLastName = 'Svátek';
        $orderData->companyName = 'BestCompanyEver, s.r.o.';
        $orderData->companyNumber = '555555';
        $orderData->note = 'Doufám, že vše dorazí v pořádku a co nejdříve :)';
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -13 day'))->setTime(17, 34, 40);
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR, Administrator::class);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '17' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 2,
            ],
            TransportDataFixture::TRANSPORT_DRONE,
            PaymentDataFixture::PAYMENT_CARD,
        );

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain(
            'vitek@shopsys.com',
            $domainId,
        );
        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'Radim';
        $orderData->lastName = 'Svátek';
        $orderData->email = 'vitek@shopsys.com';
        $orderData->telephone = '+420733598748';
        $orderData->street = 'Křivá 11';
        $orderData->city = 'Jablonec';
        $orderData->postcode = '78952';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryCity = 'Ostrava';
        $orderData->deliveryCompanyName = 'BestCompanyEver, s.r.o.';
        $orderData->deliveryCountry = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->deliveryStreet = 'Křivá 11';
        $orderData->deliveryTelephone = '+421555444';
        $orderData->deliveryPostcode = '01305';
        $orderData->deliveryFirstName = 'Pavol';
        $orderData->deliveryLastName = 'Svátek';
        $orderData->companyName = 'BestCompanyEver, s.r.o.';
        $orderData->companyNumber = '555555';
        $orderData->note = 'Doufám, že vše dorazí v pořádku a co nejdříve :)';
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -6 day'))->setTime(5, 7, 38);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '17' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 2,
            ],
            TransportDataFixture::TRANSPORT_PPL,
            PaymentDataFixture::PAYMENT_CARD,
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'NotRegistered';
        $orderData->lastName = 'User';
        $orderData->email = 'not-registered-user@shopsys.com';
        $orderData->telephone = '+421733598748';
        $orderData->street = 'Křivá 12';
        $orderData->city = 'Jablonec';
        $orderData->postcode = '78952';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryCity = 'Ostrava 1';
        $orderData->deliveryCompanyName = 'BestCompanyEver, s.r.o.';
        $orderData->deliveryCountry = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->deliveryStreet = 'Křivá 11';
        $orderData->deliveryTelephone = '+421555444';
        $orderData->deliveryPostcode = '01305';
        $orderData->deliveryFirstName = 'NotRegistered';
        $orderData->deliveryLastName = 'User';
        $orderData->companyName = 'BestCompanyEver, s.r.o.';
        $orderData->companyNumber = '555555';
        $orderData->note = 'Doufám, že vše dorazí v pořádku a co nejdříve :)';
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = new DateTime('now');
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR, Administrator::class);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '17' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 2,
            ],
            TransportDataFixture::TRANSPORT_DRONE,
            PaymentDataFixture::PAYMENT_CARD,
        );
    }

    /**
     * @param int $domainId
     */
    private function loadDistinct(int $domainId): void
    {
        $domainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS, OrderStatus::class);
        $orderData->firstName = 'Václav';
        $orderData->lastName = 'Svěrkoš';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420725711368';
        $orderData->street = 'Devátá 25';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71200';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -1 day'))->setTime(22, 51, 55);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
            ],
            TransportDataFixture::TRANSPORT_CZECH_POST,
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
        );

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain(
            'no-reply.2@shopsys.com',
            $domainId,
        );
        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'Jan';
        $orderData->lastName = 'Novák';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420123456789';
        $orderData->street = 'Pouliční 11';
        $orderData->city = 'Městník';
        $orderData->postcode = '12345';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->companyName = 'shopsys s.r.o.';
        $orderData->companyNumber = '12345678';
        $orderData->companyTaxNumber = 'CZ1234567890';
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryFirstName = 'Karel';
        $orderData->deliveryLastName = 'Vesela';
        $orderData->deliveryCompanyName = 'Bestcompany';
        $orderData->deliveryTelephone = '+420987654321';
        $orderData->deliveryStreet = 'Zakopaná 42';
        $orderData->deliveryCity = 'Zemín';
        $orderData->deliveryPostcode = '54321';
        $orderData->deliveryCountry = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class);
        $orderData->note = 'Prosím o dodání do pátku. Děkuji.';
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -4 day'))->setTime(21, 23, 5);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '1' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_CASH,
            $customerUser,
        );

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain(
            'no-reply.7@shopsys.com',
            $domainId,
        );
        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'Jindřich';
        $orderData->lastName = 'Němec';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420123456789';
        $orderData->street = 'Sídlištní 3259';
        $orderData->city = 'Orlová';
        $orderData->postcode = '65421';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -4 day'))->setTime(11, 14, 2);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '2' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '4' => 4,
            ],
            TransportDataFixture::TRANSPORT_CZECH_POST,
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_CANCELED, OrderStatus::class);
        $orderData->firstName = 'Viktor';
        $orderData->lastName = 'Pátek';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420888777111';
        $orderData->street = 'Vyhlídková 88';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71201';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -7 day'))->setTime(11, 10, 47);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '3' => 10,
            ],
            TransportDataFixture::TRANSPORT_PERSONAL,
            PaymentDataFixture::PAYMENT_CASH,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'Jindřich';
        $orderData->lastName = 'Němec';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420123456789';
        $orderData->street = 'Sídlištní 3259';
        $orderData->city = 'Orlová';
        $orderData->postcode = '65421';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -7 day'))->setTime(11, 10, 47);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '2' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '4' => 4,
            ],
            TransportDataFixture::TRANSPORT_DRONE,
            PaymentDataFixture::PAYMENT_LATER,
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
        $uniqueOrderHash = '';

        $transport = $this->getReference($transportReferenceName, Transport::class);
        $payment = $this->getReference($paymentReferenceName, Payment::class);

        $inputOrderData = $this->inputOrderDataFactory->create();
        $inputOrderData->setTransport($transport);
        $inputOrderData->setPayment($payment);

        foreach ($products as $productReferenceName => $quantity) {
            $product = $this->getReference($productReferenceName, Product::class);
            $inputOrderData->addProduct($product, $quantity);
            $uniqueOrderHash .= $product->getCatnum() . '-' . $quantity;
        }

        $uniqueOrderHash .= $orderData->firstName . $orderData->lastName . $transport->getId() . $orderData->deliveryFirstName . $orderData->deliveryLastName;
        $orderData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, md5($uniqueOrderHash))->toString();

        $orderData = $this->orderProcessor->process(
            $inputOrderData,
            $orderData,
            $this->domain->getDomainConfigById($orderData->domainId),
            $customerUser,
        );

        $order = $this->createOrderFacade->createOrder($orderData, $customerUser);

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
            CustomerUserDataFixture::class,
            OrderStatusDataFixture::class,
            CountryDataFixture::class,
            SettingValueDataFixture::class,
        ];
    }
}
