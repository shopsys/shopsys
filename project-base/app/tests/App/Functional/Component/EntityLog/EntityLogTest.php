<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\EntityLog;

use App\DataFixtures\Demo\CountryDataFixture;
use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\OrderStatusDataFixture;
use App\Model\Order\OrderDataFactory;
use App\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogRepository;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Order\CreateOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Order\Processing\InputOrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class EntityLogTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    /**
     * @inject
     */
    private OrderProcessor $orderProcessor;

    /**
     * @inject
     */
    private CreateOrderFacade $createOrderFacade;

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
    private EntityLogRepository $entityLogRepository;

    /**
     * @inject
     */
    private OrderDataFactory $orderDataFactory;

    /**
     * @inject
     */
    private InputOrderDataFactory $inputOrderDataFactory;

    /**
     * @inject
     */
    private OrderItemFacade $orderItemFacade;

    public function testCreateEntity(): void
    {
        $order = $this->getNewOrder();

        $orderFromDb = $this->orderRepository->getById($order->getId());

        $this->assertCount(3, $orderFromDb->getItems());

        $logsQueryBuilder = $this->entityLogRepository->getQueryBuilderByEntityNameAndEntityId(
            EntityLogFacade::getEntityNameByEntity($orderFromDb),
            $orderFromDb->getId(),
        );

        $logs = $logsQueryBuilder->getQuery()->execute();

        $this->assertCount(4, $logs);

        $logs = array_reverse($logs);
        $this->assertSame(EntityLogActionEnum::CREATE, $logs[0]->getAction()); //order
        $this->assertSame($orderFromDb->getNumber(), $logs[0]->getEntityIdentifier());

        $this->assertSame(EntityLogActionEnum::CREATE, $logs[1]->getAction()); //product
        $this->assertSame($orderFromDb->getProductItems()[0]->getName(), $logs[1]->getEntityIdentifier());

        $this->assertSame(EntityLogActionEnum::CREATE, $logs[2]->getAction()); //payment
        $this->assertSame($orderFromDb->getPaymentItem()->getName(), $logs[2]->getEntityIdentifier());

        $this->assertSame(EntityLogActionEnum::CREATE, $logs[3]->getAction()); //transport
        $this->assertSame($orderFromDb->getTransportItem()->getName(), $logs[3]->getEntityIdentifier());
    }

    public function testRemoveEntity(): void
    {
        $order = $this->getNewOrder();

        $orderFromDb = $this->orderRepository->getById($order->getId());

        $this->assertCount(3, $orderFromDb->getItems());

        $entityId = $orderFromDb->getId();
        $entityName = EntityLogFacade::getEntityNameByEntity($orderFromDb);

        $this->em->remove($orderFromDb);
        $this->em->flush();

        $logs = $this->entityLogRepository->getEntityLogsFromLastLogCollection($entityName, $entityId);

        $this->assertCount(4, $logs);

        foreach ($logs as $log) {
            $this->assertSame(EntityLogActionEnum::DELETE, $log->getAction());
        }
    }

    public function testEditEntity(): void
    {
        $expectedNewCity = 'Las Vegas';

        $order = $this->getNewOrder();

        /** @var \App\Model\Order\Order $orderFromDb */
        $orderFromDb = $this->orderRepository->getById($order->getId());
        $this->assertCount(3, $orderFromDb->getItems());

        $entityId = $orderFromDb->getId();
        $entityName = EntityLogFacade::getEntityNameByEntity($orderFromDb);

        $expectedOldCity = $orderFromDb->getCity();
        $expectedOldStatusName = $orderFromDb->getStatus()->getName();
        $expectedOldStatusId = $orderFromDb->getStatus()->getId();

        $orderData = $this->orderDataFactory->createFromOrder($orderFromDb);
        $orderData->city = $expectedNewCity;

        $newStatus = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS, OrderStatus::class);
        $orderData->status = $newStatus;

        $this->orderFacade->edit($entityId, $orderData);

        $logs = $this->entityLogRepository->getEntityLogsFromLastLogCollection($entityName, $entityId);

        /** @var \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog $log */
        $log = reset($logs);

        $this->assertSame(EntityLogActionEnum::UPDATE, $log->getAction());
        $this->assertSame($orderFromDb->getId(), $log->getEntityId());
        $this->assertSame($orderFromDb->getNumber(), $log->getEntityIdentifier());
        $this->assertArrayHasKey('city', $log->getChangeSet());
        $this->assertArrayHasKey('status', $log->getChangeSet());
        $this->assertSame($expectedOldCity, $log->getChangeSet()['city']['oldReadableValue']);
        $this->assertSame($expectedNewCity, $log->getChangeSet()['city']['newReadableValue']);
        $this->assertSame($expectedOldStatusId, $log->getChangeSet()['status']['oldValue']);
        $this->assertSame($expectedOldStatusName, $log->getChangeSet()['status']['oldReadableValue']);
        $this->assertSame($newStatus->getId(), $log->getChangeSet()['status']['newValue']);
        $this->assertSame($newStatus->getName(), $log->getChangeSet()['status']['newReadableValue']);
    }

    public function testEditCollectionEntity(): void
    {
        $productTicketName = '100 Czech crowns ticket';

        $order = $this->getNewOrder();

        /** @var \App\Model\Order\Order $orderFromDb */
        $orderFromDb = $this->orderRepository->getById($order->getId());

        $entityId = $orderFromDb->getId();
        $entityName = EntityLogFacade::getEntityNameByEntity($orderFromDb);

        $this->orderItemFacade->addProductToOrder($entityId, 72);

        $logs = $this->entityLogRepository->getEntityLogsFromLastLogCollection($entityName, $entityId);
        $orderLogs = array_filter($logs, fn (EntityLog $log) => $log->getEntityName() === 'Order');
        /** @var \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog $orderLog */
        $orderLog = reset($orderLogs);

        $orderItemLogs = array_filter($logs, fn (EntityLog $log) => $log->getEntityName() === 'OrderItem');
        /** @var \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog $orderItemLog */
        $orderItemLog = reset($orderItemLogs);

        $this->assertSame(EntityLogActionEnum::CREATE, $orderItemLog->getAction());
        $this->assertSame($productTicketName, $orderItemLog->getEntityIdentifier());

        $this->assertSame($orderLog->getEntityName(), $orderItemLog->getParentEntityName());
        $this->assertSame($orderLog->getEntityId(), $orderItemLog->getParentEntityId());
        $this->assertSame($order->getNumber(), $orderLog->getEntityIdentifier());

        $this->assertArrayHasKey('items', $orderLog->getChangeSet());
        $this->assertSame('Collection', $orderLog->getChangeSet()['items']['dataType']);
        $this->assertArrayHasKey(0, $orderLog->getChangeSet()['items']['insertedItems']);
        $this->assertSame($productTicketName, $orderLog->getChangeSet()['items']['insertedItems'][0]['newReadableValue']);
        $this->assertSame($orderItemLog->getEntityId(), $orderLog->getChangeSet()['items']['insertedItems'][0]['newValue']);
    }

    public function testEditOrderItem(): void
    {
        $expectedName = 'XXXXX';
        $expectedQuantity = 2;
        $expectedVatPercent = '10.000000';
        $expectedPriceWithoutVat = '127.24';

        $order = $this->getNewOrder();

        /** @var \App\Model\Order\Order $orderFromDb */
        $orderFromDb = $this->orderRepository->getById($order->getId());

        $entityId = $orderFromDb->getId();
        $entityName = EntityLogFacade::getEntityNameByEntity($orderFromDb);

        $orderData = $this->orderDataFactory->createFromOrder($orderFromDb);

        foreach ($orderData->itemsWithoutTransportAndPayment as &$itemData) {
            $itemData->name = $expectedName;
            $itemData->quantity = $expectedQuantity;
            $itemData->vatPercent = $expectedVatPercent;
        }

        $this->orderFacade->edit($entityId, $orderData);

        $logs = $this->entityLogRepository->getEntityLogsFromLastLogCollection($entityName, $entityId);

        $this->assertCount(2, $logs);
        $logs = array_filter($logs, fn (EntityLog $log) => $log->getEntityName() === 'OrderItem');
        $changeSet = reset($logs)->getChangeSet();

        $this->assertArrayHasKey('name', $changeSet);
        $this->assertArrayHasKey('priceWithoutVat', $changeSet);
        $this->assertArrayHasKey('vatPercent', $changeSet);
        $this->assertArrayHasKey('quantity', $changeSet);
        $this->assertSame($expectedName, $changeSet['name']['newReadableValue']);
        $this->assertSame($expectedPriceWithoutVat, $changeSet['priceWithoutVat']['newReadableValue']);
        $this->assertSame($expectedVatPercent, $changeSet['vatPercent']['newReadableValue']);
        $this->assertSame($expectedQuantity, $changeSet['quantity']['newReadableValue']);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    private function getNewOrder(): Order
    {
        $product = $this->productRepository->getById(1);
        $transport = $this->transportRepository->getById(3);
        $payment = $this->paymentRepository->getById(1);

        $inputOrderData = $this->inputOrderDataFactory->create();
        $inputOrderData->addProduct($product, 1);
        $inputOrderData->setTransport($transport);
        $inputOrderData->setPayment($payment);

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

        $orderData = $this->orderProcessor->process(
            $inputOrderData,
            $orderData,
            $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID),
            null,
        );

        return $this->createOrderFacade->createOrder($orderData, null);
    }
}
