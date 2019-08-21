<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Order;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\ShopBundle\DataFixtures\Demo\OrderDataFixture;
use Tests\FrameworkBundle\Test\IsMoneyEqual;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

final class OrderFacadeEditTest extends TransactionFunctionalTestCase
{
    private const ORDER_ID = 10;
    private const PRODUCT_ITEM_ID = 45;
    private const PAYMENT_ITEM_ID = 46;
    private const TRANSPORT_ITEM_ID = 47;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order
     */
    private $order;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface
     */
    private $orderDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactoryInterface
     */
    private $orderItemDataFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = $this->getReference(OrderDataFixture::ORDER_PREFIX . self::ORDER_ID);

        $this->orderFacade = $this->getContainer()->get(OrderFacade::class);
        $this->orderDataFactory = $this->getContainer()->get(OrderDataFactoryInterface::class);
        $this->orderItemDataFactory = $this->getContainer()->get(OrderItemDataFactoryInterface::class);
    }

    public function testEditProductItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->itemsWithoutTransportAndPayment[self::PRODUCT_ITEM_ID];
        $orderItemData->quantity = 10;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->priceWithVat = Money::create(100);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->order->getItemById(self::PRODUCT_ITEM_ID);
        $this->assertThat($orderItem->getPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1000)));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1011)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('676.28')));
    }

    public function testEditProductItemWithoutUsingPriceCalculation(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->itemsWithoutTransportAndPayment[self::PRODUCT_ITEM_ID];
        $orderItemData->quantity = 10;
        $orderItemData->usePriceCalculation = false;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->priceWithVat = Money::create(100);
        $orderItemData->priceWithoutVat = Money::create(50);
        $orderItemData->totalPriceWithVat = Money::create(950);
        $orderItemData->totalPriceWithoutVat = Money::create(400);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->order->getItemById(self::PRODUCT_ITEM_ID);
        $this->assertThat($orderItem->getPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(950)));
        $this->assertThat($orderItem->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(400)));

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(961)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('409.61')));
    }

    public function testAddProductItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = 'new item';
        $orderItemData->quantity = 10;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->priceWithVat = Money::create(100);
        $orderData->itemsWithoutTransportAndPayment[OrderData::NEW_ITEM_PREFIX . '1'] = $orderItemData;

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemByName($this->order, 'new item');
        $this->assertThat($orderItem->getPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1000)));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1875)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('1390.33')));
    }

    public function testAddProductItemWithoutUsingPriceCalculation(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = 'new item';
        $orderItemData->quantity = 10;
        $orderItemData->usePriceCalculation = false;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->priceWithVat = Money::create(100);
        $orderItemData->priceWithoutVat = Money::create(50);
        $orderItemData->totalPriceWithVat = Money::create(950);
        $orderItemData->totalPriceWithoutVat = Money::create(400);
        $orderData->itemsWithoutTransportAndPayment[OrderData::NEW_ITEM_PREFIX . '1'] = $orderItemData;

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemByName($this->order, 'new item');
        $this->assertThat($orderItem->getPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(950)));
        $this->assertThat($orderItem->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(400)));

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1825)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('1123.66')));
    }

    public function testEditTransportItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->orderTransport;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->priceWithVat = Money::create(100);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->order->getItemById(self::TRANSPORT_ITEM_ID);
        $this->assertThat($orderItem->getPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(967)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('783.72')));
    }

    public function testEditTransportItemWithoutUsingPriceCalculation(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->orderTransport;
        $orderItemData->usePriceCalculation = false;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->priceWithVat = Money::create(100);
        $orderItemData->priceWithoutVat = Money::create(50);
        $orderItemData->totalPriceWithVat = Money::create(100);
        $orderItemData->totalPriceWithoutVat = Money::create(50);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->order->getItemById(self::TRANSPORT_ITEM_ID);
        $this->assertThat($orderItem->getPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(967)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('767.05')));
    }

    public function testEditPaymentItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->orderPayment;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->priceWithVat = Money::create(100);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->order->getItemById(self::PAYMENT_ITEM_ID);
        $this->assertThat($orderItem->getPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(972)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('787.33')));
    }

    public function testEditPaymentItemWithoutUsingPriceCalculation(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->orderPayment;
        $orderItemData->usePriceCalculation = false;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->priceWithVat = Money::create(100);
        $orderItemData->priceWithoutVat = Money::create(50);
        $orderItemData->totalPriceWithVat = Money::create(100);
        $orderItemData->totalPriceWithoutVat = Money::create(50);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->order->getItemById(self::PAYMENT_ITEM_ID);
        $this->assertThat($orderItem->getPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(972)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('770.66')));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    private function getOrderItemByName(Order $order, string $name): OrderItem
    {
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getName() === $name) {
                return $orderItem;
            }
        }

        throw new \RuntimeException(sprintf('Order item with the name "%s" was not found in the order.', $name));
    }
}
