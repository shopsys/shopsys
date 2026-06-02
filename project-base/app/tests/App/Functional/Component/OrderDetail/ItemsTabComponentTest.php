<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\OrderDetail;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Order\Order;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent\ItemsTabComponent;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\UX\LiveComponent\LiveResponder;
use Tests\App\Test\TransactionFunctionalTestCase;

class ItemsTabComponentTest extends TransactionFunctionalTestCase
{
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
    private FormFactoryInterface $formFactory;

    /**
     * @inject
     */
    private FlashMessageService $flashMessageService;

    /**
     * @inject
     */
    private OrderItemFacade $orderItemFacade;

    /**
     * @inject
     */
    private OrderItemDataFactory $orderItemDataFactory;

    /**
     * @inject
     */
    private TransportFacade $transportFacade;

    /**
     * @inject
     */
    private PaymentFacade $paymentFacade;

    /**
     * @inject
     */
    private RequestStack $requestStack;

    /**
     * @inject
     */
    private LiveResponder $liveResponder;

    public function testAddItemKeepsEditedFormValues(): void
    {
        $component = $this->createItemsTabComponent($this->getOrder());
        $formValues = $component->formValues;
        $itemsWithoutTransportAndPayment = $formValues['orderItems']['itemsWithoutTransportAndPayment'];
        $itemKey = array_key_first($itemsWithoutTransportAndPayment);
        $itemsWithoutTransportAndPayment[$itemKey]['name'] = 'Edited before adding item';
        $formValues['orderItems']['itemsWithoutTransportAndPayment'] = $itemsWithoutTransportAndPayment;
        $component->formValues = $formValues;
        $originalItemsCount = count($itemsWithoutTransportAndPayment);

        $component->addItem();

        $itemsWithoutTransportAndPayment = $component->formValues['orderItems']['itemsWithoutTransportAndPayment'];
        $this->assertTrue($component->editMode);
        $this->assertCount($originalItemsCount + 1, $itemsWithoutTransportAndPayment);
        $this->assertSame(
            'Edited before adding item',
            $itemsWithoutTransportAndPayment[$itemKey]['name'],
        );
        $this->assertArrayHasKey('new_0', $itemsWithoutTransportAndPayment);
        $newItemFormValues = $itemsWithoutTransportAndPayment['new_0'];

        $this->assertSame('', $newItemFormValues['unitPriceWithoutVat']);
        $this->assertSame('', $newItemFormValues['vatPercent']);
        $this->assertSame('', $newItemFormValues['unitPriceWithVat']);
        $this->assertSame('', $newItemFormValues['totalPriceWithoutVat']);
        $this->assertSame('', $newItemFormValues['totalPriceWithVat']);
    }

    public function testAddItemClearsPreviousValidationState(): void
    {
        $component = $this->createItemsTabComponent($this->getOrder());
        $component->isValidated = true;
        $component->validatedFields = ['order_form.orderItems.itemsWithoutTransportAndPayment'];

        $component->addItem();

        $this->assertFalse($component->isValidated);
        $this->assertSame([], $component->validatedFields);
    }

    public function testAddProductAndSavePersistsProductAssociation(): void
    {
        $order = $this->getOrder();
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        $component = $this->createItemsTabComponent($order);
        $originalProductItemsCount = count($order->getProductItems());

        $component->addProduct($product->getId());
        $newItemFormValues = $component->formValues['orderItems']['itemsWithoutTransportAndPayment']['new_0'];
        $this->assertSame($product->getId(), $newItemFormValues['product']);

        $component->save();

        $this->em->clear();
        $updatedOrder = $this->orderFacade->getById($order->getId());
        $productItems = $updatedOrder->getProductItems();
        $lastProductItem = $productItems[array_key_last($productItems)];
        $this->assertCount($originalProductItemsCount + 1, $productItems);
        $this->assertNotNull($lastProductItem->getProduct());
        $this->assertSame($product->getId(), $lastProductItem->getProduct()->getId());
    }

    public function testRemoveItemRemovesSelectedItemButKeepsLastItem(): void
    {
        $component = $this->createItemsTabComponent($this->getOrder());
        $itemsWithoutTransportAndPayment = $component->formValues['orderItems']['itemsWithoutTransportAndPayment'];
        $itemKeys = array_keys($itemsWithoutTransportAndPayment);
        $this->assertGreaterThan(1, count($itemKeys));

        $component->removeItem((string)$itemKeys[0]);

        $itemsWithoutTransportAndPayment = $component->formValues['orderItems']['itemsWithoutTransportAndPayment'];
        $remainingItemKeys = array_keys($itemsWithoutTransportAndPayment);
        $this->assertCount(count($itemKeys) - 1, $remainingItemKeys);
        $this->assertArrayNotHasKey($itemKeys[0], $itemsWithoutTransportAndPayment);

        foreach (array_slice($remainingItemKeys, 1) as $itemKey) {
            $component->removeItem((string)$itemKey);
        }

        $itemsWithoutTransportAndPayment = $component->formValues['orderItems']['itemsWithoutTransportAndPayment'];
        $singleItemKey = (string)array_key_first($itemsWithoutTransportAndPayment);
        $component->removeItem($singleItemKey);

        $itemsWithoutTransportAndPayment = $component->formValues['orderItems']['itemsWithoutTransportAndPayment'];
        $this->assertCount(1, $itemsWithoutTransportAndPayment);
    }

    public function testPrefillTransportAndPaymentUpdatesFormValues(): void
    {
        $order = $this->getOrder();
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class);
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY, Payment::class);
        $component = $this->createItemsTabComponent($order);

        $component->prefillTransport($transport->getId());
        $component->prefillPayment($payment->getId());

        $orderTransportFormValues = $component->formValues['orderItems']['orderTransport'];
        $orderPaymentFormValues = $component->formValues['orderItems']['orderPayment'];

        $this->assertSame('1', $orderTransportFormValues['setPricesManually']);
        $this->assertSame('', $orderTransportFormValues['unitPriceWithoutVat']);
        $this->assertNotSame('', $orderTransportFormValues['unitPriceWithVat']);
        $this->assertSame(
            $transport->getTransportDomain($order->getDomainId())->getVat()->getPercent(),
            $orderTransportFormValues['vatPercent'],
        );

        $this->assertSame('1', $orderPaymentFormValues['setPricesManually']);
        $this->assertSame('', $orderPaymentFormValues['unitPriceWithoutVat']);
        $this->assertNotSame('', $orderPaymentFormValues['unitPriceWithVat']);
        $this->assertSame(
            $payment->getPaymentDomain($order->getDomainId())->getVat()->getPercent(),
            $orderPaymentFormValues['vatPercent'],
        );
    }

    public function testInvalidSaveKeepsEditModeAndReportsValidationErrors(): void
    {
        $component = $this->createItemsTabComponent($this->getOrder());

        $component->addItem();

        try {
            $component->save();
            $this->fail('Invalid order items form should fail validation.');
        } catch (UnprocessableEntityHttpException $exception) {
            $this->assertStringContainsString('Please enter name', $exception->getMessage());
            $this->assertStringContainsString('Please enter unit price with VAT', $exception->getMessage());
            $this->assertStringContainsString('Please enter VAT rate', $exception->getMessage());
        }

        $this->assertTrue($component->editMode);
    }

    private function getOrder(): Order
    {
        return $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
    }

    private function createItemsTabComponent(Order $order): ItemsTabComponent
    {
        $this->initializeSessionForComponentMount();

        $component = new ItemsTabComponent(
            $this->orderFacade,
            $this->orderDataFactory,
            $this->formFactory,
            $this->flashMessageService,
            $this->orderItemFacade,
            $this->orderItemDataFactory,
            $this->transportFacade,
            $this->paymentFacade,
        );

        $component->orderId = $order->getId();
        $component->setLiveResponder($this->liveResponder);
        $component->initializeForm([]);

        return $component;
    }

    private function initializeSessionForComponentMount(): void
    {
        $request = Request::create('/');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->requestStack->push($request);
    }
}
