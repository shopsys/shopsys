<?php

declare(strict_types=1);

namespace App\DataFixtures\Performance;

use App\DataFixtures\Demo\CountryDataFixture;
use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\OrderStatusDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Performance\CustomerUserDataFixture as PerformanceUserDataFixture;
use App\DataFixtures\Performance\ProductDataFixture as PerformanceProductDataFixture;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator as Faker;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\Console\Output\OutputInterface;

class OrderDataFixture
{
    protected const PERCENTAGE_OF_ORDERS_BY_REGISTERED_USERS = 25;

    protected const BATCH_SIZE = 10;

    /**
     * @var int
     */
    private $orderTotalCount;

    /**
     * @var int
     */
    private $orderItemCountPerOrder;

    /**
     * @var int[]
     */
    private $performanceProductIds;

    /**
     * @var int[]
     */
    private $performanceUserIds;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    private $sqlLoggerFacade;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     */
    private $persistentReferenceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory
     */
    private $orderPreviewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    private $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory
     */
    private $progressBarFactory;

    /**
     * @var \App\Model\Order\OrderDataFactory
     */
    private $orderDataFactory;

    /**
     * @param int $orderTotalCount
     * @param int $orderItemCountPerOrder
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactory
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     */
    public function __construct(
        $orderTotalCount,
        $orderItemCountPerOrder,
        EntityManagerInterface $em,
        SqlLoggerFacade $sqlLoggerFacade,
        Faker $faker,
        PersistentReferenceFacade $persistentReferenceFacade,
        OrderFacade $orderFacade,
        OrderPreviewFactory $orderPreviewFactory,
        ProductFacade $productFacade,
        CustomerUserFacade $customerUserFacade,
        ProgressBarFactory $progressBarFactory,
        OrderDataFactoryInterface $orderDataFactory
    ) {
        $this->orderTotalCount = $orderTotalCount;
        $this->orderItemCountPerOrder = $orderItemCountPerOrder;
        $this->performanceProductIds = [];
        $this->em = $em;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->faker = $faker;
        $this->persistentReferenceFacade = $persistentReferenceFacade;
        $this->orderFacade = $orderFacade;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->productFacade = $productFacade;
        $this->customerUserFacade = $customerUserFacade;
        $this->progressBarFactory = $progressBarFactory;
        $this->orderDataFactory = $orderDataFactory;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function load(OutputInterface $output)
    {
        // Sql logging during mass data import makes memory leak
        $this->sqlLoggerFacade->temporarilyDisableLogging();

        $this->loadPerformanceProductIds();
        $this->loadPerformanceUserIdsOnFirstDomain();

        $progressBar = $this->progressBarFactory->create($output, $this->orderTotalCount);

        for ($orderIndex = 0; $orderIndex < $this->orderTotalCount; $orderIndex++) {
            $this->createOrder();

            $progressBar->advance();

            if ($orderIndex % self::BATCH_SIZE === 0) {
                $this->em->clear();
            }
        }

        $progressBar->finish();

        $this->sqlLoggerFacade->reenableLogging();
    }

    private function createOrder()
    {
        $customerUser = $this->getRandomCustomerUserOrNull();
        $orderData = $this->createOrderData($customerUser);
        $quantifiedProducts = $this->createQuantifiedProducts();

        $orderPreview = $this->orderPreviewFactory->create(
            $orderData->currency,
            $orderData->domainId,
            $quantifiedProducts,
            $orderData->transport,
            $orderData->payment,
            $customerUser,
            null
        );

        $this->orderFacade->createOrder($orderData, $orderPreview, $customerUser);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     *
     * @return \App\Model\Order\OrderData
     */
    private function createOrderData(?CustomerUser $customerUser = null)
    {
        $orderData = $this->orderDataFactory->create();

        if ($customerUser !== null) {
            $orderData->firstName = $customerUser->getFirstName();
            $orderData->lastName = $customerUser->getLastName();
            $orderData->email = $customerUser->getEmail();

            $billingAddress = $customerUser->getCustomer()->getBillingAddress();
            $orderData->telephone = $customerUser->getTelephone();
            $orderData->street = $billingAddress->getStreet();
            $orderData->city = $billingAddress->getCity();
            $orderData->postcode = $billingAddress->getPostcode();
            $orderData->country = $billingAddress->getCountry();
            $orderData->companyName = $billingAddress->getCompanyName();
            $orderData->companyNumber = $billingAddress->getCompanyNumber();
            $orderData->companyTaxNumber = $billingAddress->getCompanyTaxNumber();
        } else {
            $orderData->firstName = $this->faker->firstName;
            $orderData->lastName = $this->faker->lastName;
            $orderData->email = $this->faker->safeEmail;
            $orderData->telephone = $this->faker->phoneNumber;
            $orderData->street = $this->faker->streetAddress;
            $orderData->city = $this->faker->city;
            $orderData->postcode = $this->faker->postcode;
            $orderData->country = $this->getRandomCountryFromFirstDomain();
            $orderData->companyName = $this->faker->company;
            $orderData->companyNumber = (string)$this->faker->randomNumber(6);
            $orderData->companyTaxNumber = (string)$this->faker->randomNumber(6);
        }

        $orderData->transport = $this->getRandomTransport();
        $orderData->payment = $this->getRandomPayment();
        $orderData->status = $this->persistentReferenceFacade->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE);
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryFirstName = $this->faker->firstName;
        $orderData->deliveryLastName = $this->faker->lastName;
        $orderData->deliveryCompanyName = $this->faker->company;
        $orderData->deliveryTelephone = $this->faker->phoneNumber;
        $orderData->deliveryStreet = $this->faker->streetAddress;
        $orderData->deliveryCity = $this->faker->city;
        $orderData->deliveryPostcode = $this->faker->postcode;
        $orderData->deliveryCountry = $this->getRandomCountryFromFirstDomain();
        $orderData->note = $this->faker->text(200);
        $orderData->createdAt = $this->faker->dateTimeBetween('-1 year', 'now');
        $orderData->domainId = Domain::FIRST_DOMAIN_ID;
        $orderData->currency = $this->persistentReferenceFacade->getReference(CurrencyDataFixture::CURRENCY_CZK);

        return $orderData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    private function createQuantifiedProducts()
    {
        $quantifiedProducts = [];

        $randomProductIds = $this->getRandomPerformanceProductIds($this->orderItemCountPerOrder);
        foreach ($randomProductIds as $randomProductId) {
            $product = $this->productFacade->getById($randomProductId);
            $quantity = $this->faker->numberBetween(1, 10);

            $quantifiedProducts[] = new QuantifiedProduct($product, $quantity);
        }

        return $quantifiedProducts;
    }

    private function loadPerformanceProductIds()
    {
        /** @var \App\Model\Product\Product $firstPerformanceProduct */
        $firstPerformanceProduct = $this->persistentReferenceFacade->getReference(
            PerformanceProductDataFixture::FIRST_PERFORMANCE_PRODUCT
        );

        $qb = $this->em->createQueryBuilder()
            ->select('p.id')
            ->from(Product::class, 'p')
            ->where('p.id >= :firstPerformanceProductId')
            ->andWhere('p.variantType != :mainVariantType')
            ->setParameter('firstPerformanceProductId', $firstPerformanceProduct->getId())
            ->setParameter('mainVariantType', Product::VARIANT_TYPE_MAIN);

        $this->performanceProductIds = array_column($qb->getQuery()->getScalarResult(), 'id');
    }

    /**
     * @param int $count
     * @return int[]
     */
    private function getRandomPerformanceProductIds($count)
    {
        return $this->faker->randomElements($this->performanceProductIds, $count);
    }

    private function loadPerformanceUserIdsOnFirstDomain()
    {
        /** @var \App\Model\Customer\User\CustomerUser $firstPerformanceUser */
        $firstPerformanceUser = $this->persistentReferenceFacade->getReference(
            PerformanceUserDataFixture::FIRST_PERFORMANCE_USER
        );

        $qb = $this->em->createQueryBuilder()
            ->select('u.id')
            ->from(CustomerUser::class, 'u')
            ->where('u.id >= :firstPerformanceUserId')
            ->andWhere('u.domainId = :domainId')
            ->setParameter('firstPerformanceUserId', $firstPerformanceUser->getId())
            ->setParameter('domainId', 1);

        $this->performanceUserIds = array_column($qb->getQuery()->getScalarResult(), 'id');
    }

    /**
     * @return \App\Model\Customer\User\CustomerUser|null
     */
    private function getRandomCustomerUserOrNull()
    {
        $shouldBeRegisteredUser = $this->faker->boolean(self::PERCENTAGE_OF_ORDERS_BY_REGISTERED_USERS);

        if ($shouldBeRegisteredUser) {
            $customerUserId = $this->faker->randomElement($this->performanceUserIds);
            /** @var \App\Model\Customer\User\CustomerUser $customerUser */
            $customerUser = $this->customerUserFacade->getCustomerUserById($customerUserId);

            return $customerUser;
        } else {
            return null;
        }
    }

    /**
     * @return \App\Model\Transport\Transport
     */
    private function getRandomTransport()
    {
        $randomTransportReferenceName = $this->faker->randomElement([
            TransportDataFixture::TRANSPORT_CZECH_POST,
            TransportDataFixture::TRANSPORT_PPL,
            TransportDataFixture::TRANSPORT_PERSONAL,
        ]);

        /** @var \App\Model\Transport\Transport $randomTransport */
        $randomTransport = $this->persistentReferenceFacade->getReference($randomTransportReferenceName);
        return $randomTransport;
    }

    /**
     * @return \App\Model\Payment\Payment
     */
    private function getRandomPayment()
    {
        $randomPaymentReferenceName = $this->faker->randomElement([
            PaymentDataFixture::PAYMENT_CARD,
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
            PaymentDataFixture::PAYMENT_CASH,
        ]);

        /** @var \App\Model\Payment\Payment $randomPayment */
        $randomPayment = $this->persistentReferenceFacade->getReference($randomPaymentReferenceName);
        return $randomPayment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    private function getRandomCountryFromFirstDomain()
    {
        $randomCountryReferenceName = $this->faker->randomElement([
            CountryDataFixture::COUNTRY_CZECH_REPUBLIC,
            CountryDataFixture::COUNTRY_SLOVAKIA,
        ]);

        /** @var \Shopsys\FrameworkBundle\Model\Country\Country $randomCountry */
        $randomCountry = $this->persistentReferenceFacade->getReference($randomCountryReferenceName);
        return $randomCountry;
    }
}
