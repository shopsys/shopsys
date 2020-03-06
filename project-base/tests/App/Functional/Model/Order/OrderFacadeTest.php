<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order;

use App\DataFixtures\Demo\CountryDataFixture;
use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\OrderStatusDataFixture;
use App\Model\Order\Item\OrderItemData;
use App\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class OrderFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     * @inject
     */
    private $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     * @inject
     */
    private $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory
     * @inject
     */
    private $orderPreviewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderRepository
     * @inject
     */
    private $orderRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     * @inject
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportRepository
     * @inject
     */
    private $transportRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentRepository
     * @inject
     */
    private $paymentRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     * @inject
     */
    private $persistentReferenceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface
     * @inject
     */
    private $orderDataFactory;

    public function testCreate()
    {
        $product = $this->productRepository->getById(1);

        $this->cartFacade->addProductToCart($product->getId(), 1);

        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->transportRepository->getById(1);
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->paymentRepository->getById(1);

        $orderData = new OrderData();
        $orderData->transport = $transport;
        $orderData->payment = $payment;
        $orderData->status = $this->persistentReferenceFacade->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $orderData->firstName = 'firstName';
        $orderData->lastName = 'lastName';
        $orderData->email = 'email';
        $orderData->telephone = 'telephone';
        $orderData->companyName = 'companyName';
        $orderData->companyNumber = 'companyNumber';
        $orderData->companyTaxNumber = 'companyTaxNumber';
        $orderData->street = 'street';
        $orderData->city = 'city';
        $orderData->postcode = 'postcode';
        $orderData->country = $this->persistentReferenceFacade->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryFirstName = 'deliveryFirstName';
        $orderData->deliveryLastName = 'deliveryLastName';
        $orderData->deliveryCompanyName = 'deliveryCompanyName';
        $orderData->deliveryTelephone = 'deliveryTelephone';
        $orderData->deliveryStreet = 'deliveryStreet';
        $orderData->deliveryCity = 'deliveryCity';
        $orderData->deliveryPostcode = 'deliveryPostcode';
        $orderData->deliveryCountry = $this->persistentReferenceFacade->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $orderData->note = 'note';
        $orderData->domainId = Domain::FIRST_DOMAIN_ID;
        $orderData->currency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);

        $orderPreview = $this->orderPreviewFactory->createForCurrentUser($transport, $payment);
        $order = $this->orderFacade->createOrder($orderData, $orderPreview, null);

        $orderFromDb = $this->orderRepository->getById($order->getId());

        $this->assertSame($orderData->transport->getId(), $orderFromDb->getTransport()->getId());
        $this->assertSame($orderData->payment->getId(), $orderFromDb->getPayment()->getId());
        $this->assertSame($orderData->firstName, $orderFromDb->getFirstName());
        $this->assertSame($orderData->lastName, $orderFromDb->getLastName());
        $this->assertSame($orderData->email, $orderFromDb->getEmail());
        $this->assertSame($orderData->telephone, $orderFromDb->getTelephone());
        $this->assertSame($orderData->companyName, $orderFromDb->getCompanyName());
        $this->assertSame($orderData->companyNumber, $orderFromDb->getCompanyNumber());
        $this->assertSame($orderData->companyTaxNumber, $orderFromDb->getCompanyTaxNumber());
        $this->assertSame($orderData->street, $orderFromDb->getStreet());
        $this->assertSame($orderData->city, $orderFromDb->getCity());
        $this->assertSame($orderData->postcode, $orderFromDb->getPostcode());
        $this->assertSame($orderData->country, $orderFromDb->getCountry());
        $this->assertSame($orderData->deliveryFirstName, $orderFromDb->getDeliveryFirstName());
        $this->assertSame($orderData->deliveryLastName, $orderFromDb->getDeliveryLastName());
        $this->assertSame($orderData->deliveryCompanyName, $orderFromDb->getDeliveryCompanyName());
        $this->assertSame($orderData->deliveryTelephone, $orderFromDb->getDeliveryTelephone());
        $this->assertSame($orderData->deliveryStreet, $orderFromDb->getDeliveryStreet());
        $this->assertSame($orderData->deliveryCity, $orderFromDb->getDeliveryCity());
        $this->assertSame($orderData->deliveryPostcode, $orderFromDb->getDeliveryPostcode());
        $this->assertSame($orderData->deliveryCountry, $orderFromDb->getDeliveryCountry());
        $this->assertSame($orderData->note, $orderFromDb->getNote());
        $this->assertSame($orderData->domainId, $orderFromDb->getDomainId());

        $this->assertCount(3, $orderFromDb->getItems());
    }

    public function testEdit()
    {
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference('order_1');

        $this->assertCount(4, $order->getItems());

        $orderData = $this->orderDataFactory->createFromOrder($order);

        $orderItemsData = $orderData->itemsWithoutTransportAndPayment;
        array_pop($orderItemsData);

        $orderItemData1 = new OrderItemData();
        $orderItemData1->name = 'itemName1';
        $orderItemData1->priceWithoutVat = Money::create(100);
        $orderItemData1->priceWithVat = Money::create(121);
        $orderItemData1->vatPercent = '21';
        $orderItemData1->quantity = 3;

        $orderItemData2 = new OrderItemData();
        $orderItemData2->name = 'itemName2';
        $orderItemData2->priceWithoutVat = Money::create(333);
        $orderItemData2->priceWithVat = Money::create(333);
        $orderItemData2->vatPercent = '0';
        $orderItemData2->quantity = 1;

        $orderItemsData[OrderData::NEW_ITEM_PREFIX . '1'] = $orderItemData1;
        $orderItemsData[OrderData::NEW_ITEM_PREFIX . '2'] = $orderItemData2;

        $orderData->itemsWithoutTransportAndPayment = $orderItemsData;
        $this->orderFacade->edit($order->getId(), $orderData);

        $orderFromDb = $this->orderRepository->getById($order->getId());

        $this->assertCount(5, $orderFromDb->getItems());
    }
}
