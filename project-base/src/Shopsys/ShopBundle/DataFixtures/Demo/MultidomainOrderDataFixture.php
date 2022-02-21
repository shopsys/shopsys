<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Customer\UserRepository;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\ShopBundle\DataFixtures\Demo\PaymentDataFixture as DemoPaymentDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\ProductDataFixture as DemoProductDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\TransportDataFixture as DemoTransportDataFixture;

class MultidomainOrderDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserRepository
     */
    protected $userRepository;

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
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface
     */
    protected $orderDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserRepository $userRepository
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        UserRepository $userRepository,
        Generator $faker,
        OrderFacade $orderFacade,
        OrderPreviewFactory $orderPreviewFactory,
        OrderDataFactoryInterface $orderDataFactory,
        Domain $domain
    ) {
        $this->userRepository = $userRepository;
        $this->faker = $faker;
        $this->orderFacade = $orderFacade;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->orderDataFactory = $orderDataFactory;
        $this->domain = $domain;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAllIdsExcludingFirstDomain() as $domainId) {
            $this->loadForDomain($domainId);
        }
    }

    /**
     * @param int $domainId
     */
    protected function loadForDomain(int $domainId)
    {
        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(DemoTransportDataFixture::TRANSPORT_CZECH_POST);
        $orderData->payment = $this->getReference(DemoPaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
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
        $orderData->currency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);
        $orderData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');
        $this->createOrder(
            $orderData,
            [
                DemoProductDataFixture::PRODUCT_PREFIX . '14' => 1,
            ]
        );

        $user = $this->userRepository->findUserByEmailAndDomain('no-reply.2@shopsys.com', $domainId);
        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(DemoTransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(DemoPaymentDataFixture::PAYMENT_CASH);
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
        $orderData->currency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
        $orderData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');
        $this->createOrder(
            $orderData,
            [
                DemoProductDataFixture::PRODUCT_PREFIX . '1' => 2,
                DemoProductDataFixture::PRODUCT_PREFIX . '3' => 1,
            ],
            $user
        );

        $user = $this->userRepository->findUserByEmailAndDomain('no-reply.7@shopsys.com', $domainId);
        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(DemoTransportDataFixture::TRANSPORT_CZECH_POST);
        $orderData->payment = $this->getReference(DemoPaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
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
        $orderData->currency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);
        $orderData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');
        $this->createOrder(
            $orderData,
            [
                DemoProductDataFixture::PRODUCT_PREFIX . '2' => 2,
                DemoProductDataFixture::PRODUCT_PREFIX . '4' => 4,
            ],
            $user
        );

        $orderData = $this->orderDataFactory->create();
        $orderData->transport = $this->getReference(DemoTransportDataFixture::TRANSPORT_PERSONAL);
        $orderData->payment = $this->getReference(DemoPaymentDataFixture::PAYMENT_CASH);
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
        $orderData->currency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);
        $orderData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');
        $this->createOrder(
            $orderData,
            [
                DemoProductDataFixture::PRODUCT_PREFIX . '3' => 10,
            ]
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param array $products
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     */
    protected function createOrder(
        OrderData $orderData,
        array $products,
        User $user = null
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
            $user,
            null
        );

        $order = $this->orderFacade->createOrder($orderData, $orderPreview, $user);
        /* @var $order \Shopsys\FrameworkBundle\Model\Order\Order */

        $referenceName = OrderDataFixture::ORDER_PREFIX . $order->getId();
        $this->addReference($referenceName, $order);
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            CountryDataFixture::class,
            MultidomainSettingValueDataFixture::class,
            OrderDataFixture::class,
        ];
    }
}
