<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\Order;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class OrderDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const ORDER_PREFIX = 'order_';
    private const ORDER_WITH_GOPAY_PAYMENT_PREFIX = 'order_with_gopay_payment_';
    public const ORDER_WITH_GOPAY_PAYMENT_CZ = 'order_with_gopay_payment_1';

    /**
     * @var string[]
     */
    private array $uuidPool = [
        '0338e624-c961-4475-a29d-c90080d02d1f',
        '039ab892-726d-4b6d-8c0c-dc8bd50b152b',
        '03fdb8e2-844d-436b-9f28-338720a3be16',
        '0a734071-f573-47aa-9a1e-e90be6a2e2e5',
        '0c24ac8d-b602-48c7-85a0-0cf0f0563a01',
        '0de2aade-1f29-4611-aab6-8724909b7b4c',
        '145fafe9-962b-4a2d-8206-edf26a8297fb',
        '183212ca-6621-461c-ac55-19ab506f8587',
        '1cfdf517-1615-400f-919e-1890a1fb2bb1',
        '1dd1cdc7-dc00-4d04-a8d5-d4ca77fbaee3',
        '1e34be70-f1c1-4d33-b5a6-949b026635cd',
        '1f9c3eb1-5d24-4746-acde-4b5be849b1c6',
        '25e2bf82-c351-48a0-9b5e-e579516850e6',
        '26b2a555-2bd3-4fbe-8e1c-ca758eefc10c',
        '28e991b8-6311-447a-ae6f-999626ddd4d9',
        '29708754-bbb0-4740-9beb-96674181282c',
        '2abf6457-87bc-40b7-b79e-153a9693c5e7',
        '2ba1820a-1670-4dc0-90f1-69547103a1ce',
        '2cf040a1-d627-4ff8-b156-bc8b816a447c',
        '4510ea11-2c3e-4505-a1fe-d9db7f5a716d',
        '46400d8e-cbfd-44cb-b4e6-36e5a11fd257',
        '494dff86-aa92-464d-9838-c1e8f6b5e1f9',
        '4fa29761-509a-48b1-9b2c-ce7e9dc2c6d2',
        '5719343f-98a5-4d1c-a392-b66b941b3e7f',
        '5c015914-b858-42a9-8553-679d4243df40',
        '5c6ccaea-0d61-42f0-8439-17dbefd766a9',
        '626edc89-6b54-4f88-8029-2f1508de7fd0',
        '63320911-2364-4fd7-b6a8-c5713ec14a29',
        '6d54cc67-c2cb-4061-8476-c2a15c804f32',
        '71e06550-9d0e-439c-bdc2-9443f9a1769a',
        '740ad733-b445-489a-891f-30dd62c1b3fc',
        '79116d8a-4e9a-4b4c-bc98-7ed9dab53adf',
        '7a984cf4-f5e1-4ee0-a6d7-7c333a1aae36',
        '81a72bbb-7d42-4f29-af49-e48b7dbdcc00',
        '84991302-e8c1-4f64-96b5-558e55ecd111',
        '88c32f13-8a5f-4ecf-8825-e3b32b0a3424',
        '8a30f084-0099-4d06-a844-4d75f8514f0b',
        '8d2d3d43-7437-43ef-b702-71a389632a66',
        '9d2df014-f12f-4645-9403-242adb345b6a',
        'a7924d77-9b35-426e-ae25-4000cb4a26e5',
        'a8c49a5c-698c-46ed-b48a-b598547caf05',
        'aff2c0ef-d963-4a1c-b967-a740b341d716',
        'b49ecc97-b9ef-494b-9932-aa43d5bf29db',
        'c4a7a44e-6636-46a7-977e-5a0f05b3c2ff',
        'cd3806e0-1ae3-4acb-81fd-feb30c394af9',
        'ce357436-cf09-454c-b8fb-93756b087599',
        'd945a70c-1a42-4821-b3fa-0fb94488acd7',
        'd9d770da-b2a0-4bf1-890a-cae6c8ae4a31',
        'dae26ff5-a0e9-4627-9f28-d9eb80fd77d3',
        'e111cc90-9652-4e36-8656-fc73a62cbe37',
        'e17981bc-8aa6-4fbd-b725-56b71d9deecd',
        'e2217191-7aaa-4bcd-abd5-658e26adb8ae',
        'e4002d79-0dba-4899-a51a-0a8feec3c2ce',
        'eeb2d5a4-f1b3-4374-ad4f-8c6b095be976',
        'f1a1aff2-597c-406e-801b-be0c13ee5cea',
        'f515d6ce-be2a-4061-a715-44a8aa9e8235',
        'f871644f-f0cb-4065-8736-cbc00e39d560',
        'f9fac195-5a81-4e0b-a0ba-9464cc544f3b',
        'fb4a7cfc-5d0e-44ba-ab43-0bac3acb3e64',
    ];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        private readonly CustomerUserRepository $customerUserRepository,
        private readonly OrderFacade $orderFacade,
        private readonly OrderPreviewFactory $orderPreviewFactory,
        private readonly OrderDataFactoryInterface $orderDataFactory,
        private readonly Domain $domain,
        private readonly CurrencyFacade $currencyFacade,
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
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE);
        $orderData->firstName = 'Jiří';
        $orderData->lastName = 'Ševčík';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420369554147';
        $orderData->street = 'První 1';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71200';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
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
            $customerUser,
        );
        $this->addReference(self::ORDER_WITH_GOPAY_PAYMENT_PREFIX . $domainId, $order);

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'Iva';
        $orderData->lastName = 'Jačková';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420367852147';
        $orderData->street = 'Druhá 2';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71300';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -11 day'))->setTime(4, 34, 19);
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);
        $orderData->gtmCoupon = 'promoCode123';
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '18' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '19' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '20' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '15' => 5,
            ],
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'Jan';
        $orderData->lastName = 'Adamovský';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420725852147';
        $orderData->street = 'Třetí 3';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71200';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
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
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS);
        $orderData->trackingNumber = '48976519372';
        $orderData->firstName = 'Iveta';
        $orderData->lastName = 'Prvá';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420606952147';
        $orderData->street = 'Čtvrtá 4';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '70030';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -1 day'))->setTime(18, 30, 01);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '1' => 1,
            ],
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE);
        $orderData->firstName = 'Jana';
        $orderData->lastName = 'Janíčková';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420739852148';
        $orderData->street = 'Pátá 55';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71200';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -2 day'))->setTime(1, 46, 6);
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '2' => 8,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '1' => 2,
            ],
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'Dominik';
        $orderData->lastName = 'Hašek';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420721852152';
        $orderData->street = 'Šestá 39';
        $orderData->city = 'Pardubice';
        $orderData->postcode = '58941';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
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
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_CANCELED);
        $orderData->firstName = 'Jiří';
        $orderData->lastName = 'Sovák';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420755872155';
        $orderData->street = 'Sedmá 1488';
        $orderData->city = 'Opava';
        $orderData->postcode = '85741';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
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
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE);
        $orderData->firstName = 'Josef';
        $orderData->lastName = 'Somr';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420369852147';
        $orderData->street = 'Osmá 1';
        $orderData->city = 'Praha';
        $orderData->postcode = '30258';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -5 day'))->setTime(9, 11, 59);
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '1' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '2' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '12' => 1,
            ],
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_DRONE);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_CANCELED);
        $orderData->firstName = 'Ivan';
        $orderData->lastName = 'Horník';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420755496328';
        $orderData->street = 'Desátá 10';
        $orderData->city = 'Plzeň';
        $orderData->postcode = '30010';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
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
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->trackingNumber = '1234567890';
        $orderData->firstName = 'Adam';
        $orderData->lastName = 'Bořič';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420987654321';
        $orderData->street = 'Cihelní 5';
        $orderData->city = 'Liberec';
        $orderData->postcode = '65421';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
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
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS);
        $orderData->firstName = 'Evžen';
        $orderData->lastName = 'Farný';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420456789123';
        $orderData->street = 'Gagarinova 333';
        $orderData->city = 'Hodonín';
        $orderData->postcode = '69501';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
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
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE);
        $orderData->firstName = 'Ivana';
        $orderData->lastName = 'Janečková';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420369852147';
        $orderData->street = 'Kalužní 88';
        $orderData->city = 'Lednice';
        $orderData->postcode = '69144';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
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
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'Pavel';
        $orderData->lastName = 'Novák';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420605123654';
        $orderData->street = 'Adresní 6';
        $orderData->city = 'Opava';
        $orderData->postcode = '72589';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -10 day'))->setTime(23, 47, 11);
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '10' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '20' => 4,
            ],
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE);
        $orderData->trackingNumber = '48172539041';
        $orderData->firstName = 'Pavla';
        $orderData->lastName = 'Adámková';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+4206051836459';
        $orderData->street = 'Výpočetni 16';
        $orderData->city = 'Praha';
        $orderData->postcode = '30015';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -10 day'))->setTime(8, 14, 8);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '15' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '18' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '19' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ],
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS);
        $orderData->firstName = 'Adam';
        $orderData->lastName = 'Žitný';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+4206051836459';
        $orderData->street = 'Přímá 1';
        $orderData->city = 'Plzeň';
        $orderData->postcode = '30010';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
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
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'Radim';
        $orderData->lastName = 'Svátek';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420733598748';
        $orderData->street = 'Křivá 11';
        $orderData->city = 'Jablonec';
        $orderData->postcode = '78952';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
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
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_DRONE);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'Radim';
        $orderData->lastName = 'Svátek';
        $orderData->email = 'vitek@shopsys.com';
        $orderData->telephone = '+420733598748';
        $orderData->street = 'Křivá 11';
        $orderData->city = 'Jablonec';
        $orderData->postcode = '78952';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryCity = 'Ostrava';
        $orderData->deliveryCompanyName = 'BestCompanyEver, s.r.o.';
        $orderData->deliveryCountry = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
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
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '17' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 2,
            ],
        );

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain(
            'vitek@shopsys.com',
            $domainId,
        );
        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'Radim';
        $orderData->lastName = 'Svátek';
        $orderData->email = 'vitek@shopsys.com';
        $orderData->telephone = '+420733598748';
        $orderData->street = 'Křivá 11';
        $orderData->city = 'Jablonec';
        $orderData->postcode = '78952';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryCity = 'Ostrava';
        $orderData->deliveryCompanyName = 'BestCompanyEver, s.r.o.';
        $orderData->deliveryCountry = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
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
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_DRONE);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'NotRegistered';
        $orderData->lastName = 'User';
        $orderData->email = 'not-registered-user@shopsys.com';
        $orderData->telephone = '+421733598748';
        $orderData->street = 'Křivá 12';
        $orderData->city = 'Jablonec';
        $orderData->postcode = '78952';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryCity = 'Ostrava 1';
        $orderData->deliveryCompanyName = 'BestCompanyEver, s.r.o.';
        $orderData->deliveryCountry = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
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
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '17' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 2,
            ],
        );
    }

    /**
     * @param int $domainId
     */
    private function loadDistinct(int $domainId): void
    {
        $domainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS);
        $orderData->firstName = 'Václav';
        $orderData->lastName = 'Svěrkoš';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420725711368';
        $orderData->street = 'Devátá 25';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71200';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -1 day'))->setTime(22, 51, 55);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
            ],
        );

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain(
            'no-reply.2@shopsys.com',
            $domainId,
        );
        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'Jan';
        $orderData->lastName = 'Novák';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420123456789';
        $orderData->street = 'Pouliční 11';
        $orderData->city = 'Městník';
        $orderData->postcode = '12345';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
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
        $orderData->deliveryCountry = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
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
            $customerUser,
        );

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain(
            'no-reply.7@shopsys.com',
            $domainId,
        );
        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'Jindřich';
        $orderData->lastName = 'Němec';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420123456789';
        $orderData->street = 'Sídlištní 3259';
        $orderData->city = 'Orlová';
        $orderData->postcode = '65421';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
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
            $customerUser,
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_CANCELED);
        $orderData->firstName = 'Viktor';
        $orderData->lastName = 'Pátek';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420888777111';
        $orderData->street = 'Vyhlídková 88';
        $orderData->city = 'Ostrava';
        $orderData->postcode = '71201';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = $domainId;
        $orderData->currency = $domainDefaultCurrency;
        $orderData->createdAt = (new DateTime('now -7 day'))->setTime(11, 10, 47);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '3' => 10,
            ],
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_DRONE);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_LATER);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'Jindřich';
        $orderData->lastName = 'Němec';
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->telephone = '+420123456789';
        $orderData->street = 'Sídlištní 3259';
        $orderData->city = 'Orlová';
        $orderData->postcode = '65421';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
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
            $customerUser,
        );
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param mixed[] $products
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @return \App\Model\Order\Order
     */
    private function createOrder(
        OrderData $orderData,
        array $products,
        ?CustomerUser $customerUser = null,
    ): Order {
        $orderData->uuid = array_pop($this->uuidPool);
        $quantifiedProducts = [];

        foreach ($products as $productReferenceName => $quantity) {
            $product = $this->getReference($productReferenceName);
            $quantifiedProducts[] = new QuantifiedProduct($product, $quantity);
        }
        $orderPreview = $this->orderPreviewFactory->create(
            $orderData->currency,
            $orderData->domainId,
            $quantifiedProducts,
            $orderData->transport,
            $orderData->payment,
            $customerUser,
            null,
        );

        /** @var \App\Model\Order\Order $order */
        $order = $this->orderFacade->createOrder($orderData, $orderPreview, $customerUser);
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
