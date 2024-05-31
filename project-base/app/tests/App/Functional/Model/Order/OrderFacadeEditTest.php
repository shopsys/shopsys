<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\Item\OrderItemDataFactory;
use App\Model\Order\Order;
use App\Model\Order\OrderDataFactory;
use App\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Tests\App\Test\TransactionFunctionalTestCase;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

final class OrderFacadeEditTest extends TransactionFunctionalTestCase
{
    private const int ORDER_ID = 10;
    private const int PRODUCT_ITEM_ID = 45;
    private const int PAYMENT_ITEM_ID = 46;
    private const int TRANSPORT_ITEM_ID = 47;

    private Order $order;

    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    /**
     * @inject
     */
    private OrderDataFactory $orderDataFactory;

    /**
     * @inject
     */
    private OrderItemDataFactory $orderItemDataFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setOrderForTests();
    }

    public function testEditProductItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT)[0];
        $orderItemData->quantity = 10;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemById($this->order, self::PRODUCT_ITEM_ID);
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1000)));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1342)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('966.67')));
    }

    public function testEditProductItemWithoutUsingPriceCalculation(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT)[0];
        $orderItemData->quantity = 10;
        $orderItemData->usePriceCalculation = false;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);
        $orderItemData->unitPriceWithoutVat = Money::create(50);
        $orderItemData->totalPriceWithVat = Money::create(950);
        $orderItemData->totalPriceWithoutVat = Money::create(400);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemById($this->order, self::PRODUCT_ITEM_ID);
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(950)));
        $this->assertThat($orderItem->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(400)));

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1292)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(700)));
    }

    public function testAddProductItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PRODUCT);
        $orderItemData->name = 'new item';
        $orderItemData->quantity = 10;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);
        $orderData->items[OrderData::NEW_ITEM_PREFIX . '1'] = $orderItemData;

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemByName($this->order, 'new item');
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1000)));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(22932)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('18809.65')));
    }

    public function testAddProductItemWithoutUsingPriceCalculation(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PRODUCT);
        $orderItemData->name = 'new item';
        $orderItemData->quantity = 10;
        $orderItemData->usePriceCalculation = false;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);
        $orderItemData->unitPriceWithoutVat = Money::create(50);
        $orderItemData->totalPriceWithVat = Money::create(950);
        $orderItemData->totalPriceWithoutVat = Money::create(400);
        $orderData->items[OrderData::NEW_ITEM_PREFIX . '1'] = $orderItemData;

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemByName($this->order, 'new item');
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(950)));
        $this->assertThat($orderItem->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(400)));

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(22882)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('18542.98')));
    }

    public function testEditTransportItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->orderTransport;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemById($this->order, self::TRANSPORT_ITEM_ID);
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(21790)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('18009.65')));
    }

    public function testEditTransportItemWithoutUsingPriceCalculation(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->orderTransport;
        $orderItemData->usePriceCalculation = false;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);
        $orderItemData->unitPriceWithoutVat = Money::create(50);
        $orderItemData->totalPriceWithVat = Money::create(100);
        $orderItemData->totalPriceWithoutVat = Money::create(50);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemById($this->order, self::TRANSPORT_ITEM_ID);
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(21790)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('17992.98')));
    }

    public function testEditPaymentItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->orderPayment;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemById($this->order, self::PAYMENT_ITEM_ID);
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(21932)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('18109.65')));
    }

    public function testEditPaymentItemWithoutUsingPriceCalculation(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->orderPayment;
        $orderItemData->usePriceCalculation = false;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);
        $orderItemData->unitPriceWithoutVat = Money::create(50);
        $orderItemData->totalPriceWithVat = Money::create(100);
        $orderItemData->totalPriceWithoutVat = Money::create(50);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemById($this->order, self::PAYMENT_ITEM_ID);
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));

        $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(21932)));
        $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('18092.98')));
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param string $name
     * @return \App\Model\Order\Item\OrderItem
     */
    private function getOrderItemByName(Order $order, string $name): OrderItem
    {
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getName() === $name) {
                return $orderItem;
            }
        }

        throw new OrderItemNotFoundException(sprintf(
            'Order item with the name "%s" was not found in the order.',
            $name,
        ));
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param int $orderItemId
     * @return \App\Model\Order\Item\OrderItem
     */
    private function getOrderItemById(Order $order, int $orderItemId): OrderItem
    {
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getId() === $orderItemId) {
                return $orderItem;
            }
        }

        throw new OrderItemNotFoundException(sprintf(
            'Order item id `%d` not found.',
            $orderItemId,
        ));
    }

    protected function setOrderForTests(): void
    {
        $this->order = $this->getReference(OrderDataFixture::ORDER_PREFIX . self::ORDER_ID, Order::class);
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT)[0];
        $orderItemData->unitPriceWithVat = Money::create(21590);

        $orderPayment = $orderData->orderPayment;
        $orderPayment->unitPriceWithVat = Money::create(100);

        $orderTransport = $orderData->orderTransport;
        $orderTransport->unitPriceWithVat = Money::create(242);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);
    }
}
