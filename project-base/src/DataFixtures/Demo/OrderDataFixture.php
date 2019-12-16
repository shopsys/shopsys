<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class OrderDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const ORDER_PREFIX = 'order_';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserRepository
     */
    protected $customerUserRepository;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     */
    protected $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory
     */
    protected $orderPreviewFactory;

    /**
     * @var \App\Model\Order\OrderDataFactory
     */
    protected $orderDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserRepository $customerUserRepository
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        CustomerUserRepository $customerUserRepository,
        Generator $faker,
        OrderFacade $orderFacade,
        OrderPreviewFactory $orderPreviewFactory,
        OrderDataFactoryInterface $orderDataFactory,
        Domain $domain,
        CurrencyFacade $currencyFacade
    ) {
        $this->customerUserRepository = $customerUserRepository;
        $this->faker = $faker;
        $this->orderFacade = $orderFacade;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->orderDataFactory = $orderDataFactory;
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
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
    protected function loadDefault(int $domainId): void
    {
        $domainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain('no-reply@shopsys.com', $domainId);
        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '9' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 3,
            ],
            $customerUser
        );

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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '18' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '19' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '20' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '15' => 5,
            ],
            $customerUser
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '4' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '11' => 1,
            ],
            $customerUser
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS);
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '1' => 1,
            ],
            $customerUser
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '2' => 8,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '1' => 2,
            ],
            $customerUser
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
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
            $customerUser
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '8' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '12' => 2,
            ]
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '1' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '2' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '12' => 1,
            ]
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '9' => 3,
                ProductDataFixture::PRODUCT_PREFIX . '13' => 2,
            ]
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ]
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '1' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '2' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ]
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '4' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ]
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '10' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '20' => 4,
            ]
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $orderData->payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE);
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '15' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '18' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '19' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ]
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '19' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '6' => 1,
            ]
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '17' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 2,
            ]
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $orderData->createdAsAdministrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '17' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 2,
            ]
        );

        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain('vitek@shopsys.com', $domainId);
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-2 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '7' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '17' => 6,
                ProductDataFixture::PRODUCT_PREFIX . '9' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
                ProductDataFixture::PRODUCT_PREFIX . '10' => 2,
            ],
            $customerUser
        );
    }

    /**
     * @param int $domainId
     */
    protected function loadDistinct(int $domainId)
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '14' => 1,
            ]
        );

        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain('no-reply.2@shopsys.com', $domainId);
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
        $orderData->companyNumber = '123456789';
        $orderData->companyTaxNumber = '987654321';
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '1' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ],
            $customerUser
        );

        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain('no-reply.7@shopsys.com', $domainId);
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '2' => 2,
                ProductDataFixture::PRODUCT_PREFIX . '4' => 4,
            ],
            $customerUser
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
        $orderData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');
        $this->createOrder(
            $orderData,
            [
                ProductDataFixture::PRODUCT_PREFIX . '3' => 10,
            ]
        );
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param array $products
     * @param \App\Model\Customer\CustomerUser $customerUser
     */
    protected function createOrder(
        OrderData $orderData,
        array $products,
        ?CustomerUser $customerUser = null
    ) {
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
            null
        );

        $order = $this->orderFacade->createOrder($orderData, $orderPreview, $customerUser);
        /* @var $order \App\Model\Order\Order */

        $referenceName = self::ORDER_PREFIX . $order->getId();
        $this->addReference($referenceName, $order);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
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
