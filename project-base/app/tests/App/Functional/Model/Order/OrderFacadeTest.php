<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order;

use App\DataFixtures\Demo\CountryDataFixture;
use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\OrderStatusDataFixture;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\Item\OrderItemData;
use App\Model\Order\Order;
use App\Model\Order\OrderData;
use App\Model\Order\OrderDataFactory;
use App\Model\Order\Status\OrderStatus;
use App\Model\Payment\PaymentRepository;
use App\Model\Product\ProductRepository;
use App\Model\Transport\TransportRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Tests\App\Test\TransactionFunctionalTestCase;

class OrderFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    /**
     * @inject
     */
    private OrderRepository $orderRepository;

    /**
     * @inject
     */
    private ProductRepository $productRepository;

    /**
     * @inject
     */
    private TransportRepository $transportRepository;

    /**
     * @inject
     */
    private PaymentRepository $paymentRepository;

    /**
     * @inject
     */
    private OrderDataFactory $orderDataFactory;

    /**
     * @inject
     */
    private OrderInputFactory $orderInputFactory;

    /**
     * @inject
     */
    private OrderProcessor $orderProcessor;

    /**
     * @inject
     */
    private PlaceOrderFacade $placeOrderFacade;

    public function testCreate(): void
    {
        $product = $this->productRepository->getById(1);
        $transport = $this->transportRepository->getById(3);
        $payment = $this->paymentRepository->getById(1);

        $orderData = $this->orderDataFactory->create();

        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'firstName';
        $orderData->lastName = 'lastName';
        $orderData->email = 'email';
        $orderData->telephone = 'telephone';
        $orderData->companyName = null;
        $orderData->companyNumber = null;
        $orderData->companyTaxNumber = null;
        $orderData->street = 'street';
        $orderData->city = 'city';
        $orderData->postcode = 'postcode';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryFirstName = 'deliveryFirstName';
        $orderData->deliveryLastName = 'deliveryLastName';
        $orderData->deliveryCompanyName = 'deliveryCompanyName';
        $orderData->deliveryTelephone = 'deliveryTelephone';
        $orderData->deliveryStreet = 'deliveryStreet';
        $orderData->deliveryCity = 'deliveryCity';
        $orderData->deliveryPostcode = 'deliveryPostcode';
        $orderData->deliveryCountry = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->note = 'note';
        $orderData->domainId = Domain::FIRST_DOMAIN_ID;
        $orderData->currency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        $orderInput = $this->orderInputFactory->create($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID), );
        $orderInput->addProduct($product, 1);
        $orderInput->setTransport($transport);
        $orderInput->setPayment($payment);

        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );
        $order = $this->placeOrderFacade->createOrder($orderData);

        $orderFromDb = $this->orderRepository->getById($order->getId());

        $this->assertSame($orderData->transport->getId(), $orderFromDb->getTransportItem()->getTransport()->getId());
        $this->assertSame($orderData->payment->getId(), $orderFromDb->getPaymentItem()->getPayment()->getId());
        $this->assertSame($orderData->firstName, $orderFromDb->getFirstName());
        $this->assertSame($orderData->lastName, $orderFromDb->getLastName());
        $this->assertSame($orderData->email, $orderFromDb->getEmail());
        $this->assertSame($orderData->telephone, $orderFromDb->getTelephone());
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

    public function testEdit(): void
    {
        $order = $this->getReference('order_1', Order::class);

        $this->assertCount(4, $order->getItems());

        $orderData = $this->orderDataFactory->createFromOrder($order);

        $orderItemsData = $orderData->itemsWithoutTransportAndPayment;
        array_pop($orderItemsData);

        $orderItemData1 = new OrderItemData();
        $orderItemData1->name = 'itemName1';
        $orderItemData1->unitPriceWithoutVat = Money::create(100);
        $orderItemData1->unitPriceWithVat = Money::create(121);
        $orderItemData1->vatPercent = '21';
        $orderItemData1->quantity = 3;
        $orderItemData1->type = OrderItem::TYPE_PRODUCT;

        $orderItemData2 = new OrderItemData();
        $orderItemData2->name = 'itemName2';
        $orderItemData2->unitPriceWithoutVat = Money::create(333);
        $orderItemData2->unitPriceWithVat = Money::create(333);
        $orderItemData2->vatPercent = '0';
        $orderItemData2->quantity = 1;
        $orderItemData2->type = OrderItem::TYPE_PRODUCT;

        $orderItemsData[OrderData::NEW_ITEM_PREFIX . '1'] = $orderItemData1;
        $orderItemsData[OrderData::NEW_ITEM_PREFIX . '2'] = $orderItemData2;

        $orderData->itemsWithoutTransportAndPayment = $orderItemsData;
        $this->orderFacade->edit($order->getId(), $orderData);

        $orderFromDb = $this->orderRepository->getById($order->getId());

        $this->assertCount(5, $orderFromDb->getItems());
    }
}
