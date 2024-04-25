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
use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Status\OrderStatus;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator as Faker;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\Console\Output\OutputInterface;

class OrderDataFixture
{
    private const PERCENTAGE_OF_ORDERS_BY_REGISTERED_USERS = 25;

    private const BATCH_SIZE = 10;

    /**
     * @var int[]
     */
    private array $performanceProductIds;

    /**
     * @var int[]
     */
    private array $performanceUserIds;

    /**
     * @param int $orderTotalCount
     * @param int $orderItemCountPerOrder
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactory
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor
     * @param \App\Model\Order\PlaceOrderFacade $placeOrderFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private int $orderTotalCount,
        private int $orderItemCountPerOrder,
        private readonly EntityManagerInterface $em,
        private readonly SqlLoggerFacade $sqlLoggerFacade,
        private readonly Faker $faker,
        private readonly PersistentReferenceFacade $persistentReferenceFacade,
        private readonly ProductFacade $productFacade,
        private readonly CustomerUserFacade $customerUserFacade,
        private readonly ProgressBarFactory $progressBarFactory,
        private readonly OrderDataFactory $orderDataFactory,
        private readonly OrderInputFactory $orderInputFactory,
        private readonly OrderProcessor $orderProcessor,
        private readonly PlaceOrderFacade $placeOrderFacade,
        private readonly Domain $domain,
    ) {
        $this->performanceProductIds = [];
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

        $transport = $this->getRandomTransport();
        $payment = $this->getRandomPayment();

        $orderInput = $this->orderInputFactory->create($this->domain->getDomainConfigById($orderData->domainId));
        $orderInput->setTransport($transport);
        $orderInput->setPayment($payment);
        $orderInput->setCustomerUser($customerUser);

        foreach ($quantifiedProducts as $quantifiedProduct) {
            $orderInput->addProduct(
                $quantifiedProduct->getProduct(),
                $quantifiedProduct->getQuantity(),
            );
        }

        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );

        $this->placeOrderFacade->createOrder($orderData);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
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

        $orderData->status = $this->persistentReferenceFacade->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE, OrderStatus::class);
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
        $orderData->currency = $this->persistentReferenceFacade->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

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
        $firstPerformanceProduct = $this->persistentReferenceFacade->getReference(
            PerformanceProductDataFixture::FIRST_PERFORMANCE_PRODUCT,
            Product::class,
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
        $firstPerformanceUser = $this->persistentReferenceFacade->getReference(
            PerformanceUserDataFixture::FIRST_PERFORMANCE_USER,
            CustomerUser::class,
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

        if (!$shouldBeRegisteredUser) {
            return null;
        }

        $customerUserId = $this->faker->randomElement($this->performanceUserIds);
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserFacade->getCustomerUserById($customerUserId);

        return $customerUser;
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

        $randomTransport = $this->persistentReferenceFacade->getReference($randomTransportReferenceName, Transport::class);

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

        $randomPayment = $this->persistentReferenceFacade->getReference($randomPaymentReferenceName, Payment::class);

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

        $randomCountry = $this->persistentReferenceFacade->getReference($randomCountryReferenceName, Country::class);

        return $randomCountry;
    }
}
