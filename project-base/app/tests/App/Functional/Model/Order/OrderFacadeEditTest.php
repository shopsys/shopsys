<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\Item\OrderItemDataFactory;
use App\Model\Order\Order;
use App\Model\Order\OrderDataFactory;
use App\Model\Order\OrderFacade;
use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Tests\App\Test\TransactionFunctionalTestCase;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

final class OrderFacadeEditTest extends TransactionFunctionalTestCase
{
    private const int ORDER_ID = 10;
    private const int PRODUCT_ITEM_ID = 45;
    private const int TRANSPORT_ITEM_ID = 46;
    private const int PAYMENT_ITEM_ID = 47;

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

    #[Override]
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
        $orderItemData->unitPriceWithoutVat = Money::create('66.67');

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemById($this->order, self::PRODUCT_ITEM_ID);

        if ($this->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
            $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1000)));
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1342)));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('966.67')));
        } else {
            $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create('100.01')));
            $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('1000.10')));
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('1324.74')));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('949.34')));
        }

        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());
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

        if ($this->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1292)));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(700)));
        } else {
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('1274.64')));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('682.64')));
        }
    }

    public function testAddProductItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PRODUCT);
        $orderItemData->name = 'new item';
        $orderItemData->quantity = 10;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);
        $orderItemData->unitPriceWithoutVat = Money::create('66.67');
        $orderData->addItem($orderItemData);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemByName($this->order, 'new item');
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());

        if ($this->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(22932)));
            $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(1000)));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('18809.65')));
        } else {
            $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create('100.01')));
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('22914.75')));
            $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('1000.10')));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('18792.32')));
        }
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
        $orderData->addItem($orderItemData);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemByName($this->order, 'new item');
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));
        $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(950)));
        $this->assertThat($orderItem->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(400)));

        if ($this->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(22882)));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('18542.98')));
        } else {
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('22864.65')));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('18525.62')));
        }
    }

    public function testEditTransportItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->orderTransport;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);
        $orderItemData->unitPriceWithoutVat = Money::create('66.67');

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemById($this->order, self::TRANSPORT_ITEM_ID);

        if ($this->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
            $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(21790)));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('18009.65')));
        } else {
            $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create('100.01')));
            $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('100.01')));
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('21772.66')));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('17992.29')));
        }

        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());
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

        if ($this->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(21790)));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('17992.98')));
        } else {
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('21772.65')));
            $this->assertThat($this->order->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create('17975.62')));
        }
    }

    public function testEditPaymentItem(): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($this->order);

        $orderItemData = $orderData->orderPayment;
        $orderItemData->vatPercent = '50.00';
        $orderItemData->unitPriceWithVat = Money::create(100);
        $orderItemData->unitPriceWithoutVat = Money::create('66.67');

        $this->orderFacade->edit(self::ORDER_ID, $orderData);

        $orderItem = $this->getOrderItemById($this->order, self::PAYMENT_ITEM_ID);

        if ($this->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(100)));
            $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(21932)));
        } else {
            $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create('100.01')));
            $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('100.01')));
            $this->assertThat($this->order->getTotalPriceWithVat(), new IsMoneyEqual(Money::create('21932.02')));
        }

        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create('66.67')));
        $this->assertNull($orderItem->getTotalPriceWithoutVat());

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
        $this->assertThat($orderItem->getTotalPriceWithoutVat(), new IsMoneyEqual(Money::create(50)));

        if ($this->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        } else {
            $this->assertThat($orderItem->getTotalPriceWithVat(), new IsMoneyEqual(Money::create(100)));
        }

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
        $orderItemData->unitPriceWithoutVat = Money::create('17842.98');

        $orderPayment = $orderData->orderPayment;
        $orderPayment->unitPriceWithVat = Money::create(100);
        $orderPayment->unitPriceWithoutVat = Money::create('82.64');

        $orderTransport = $orderData->orderTransport;
        $orderTransport->unitPriceWithVat = Money::create(242);
        $orderTransport->unitPriceWithoutVat = Money::create(200);

        $this->orderFacade->edit(self::ORDER_ID, $orderData);
    }
}
