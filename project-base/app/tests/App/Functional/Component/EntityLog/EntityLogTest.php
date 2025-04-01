<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\EntityLog;

use App\DataFixtures\Demo\CountryDataFixture;
use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\OrderStatusDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Order\OrderDataFactory;
use App\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogRepository;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Product;
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
    private PlaceOrderFacade $placeOrderFacade;

    /**
     * @inject
     */
    private OrderRepository $orderRepository;

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
    private OrderInputFactory $orderInputFactory;

    /**
     * @inject
     */
    private OrderItemFacade $orderItemFacade;

    /**
     * @inject
     */
    private EntityLogFacade $entityLogFacade;

    /**
     * @inject
     */
    private PriceConverter $priceConverter;

    public function testCreateEntity(): void
    {
        $order = $this->getNewOrder();

        $orderFromDb = $this->orderRepository->getById($order->getId());

        $this->assertCount(3, $orderFromDb->getItems());

        $logsQueryBuilder = $this->entityLogRepository->getQueryBuilderByEntityNameAndEntityId(
            $this->entityLogFacade->getEntityNameByEntity($orderFromDb),
            $orderFromDb->getId(),
        );

        $logs = $logsQueryBuilder->getQuery()->execute();

        $this->assertCount(4, $logs);

        $logs = array_reverse($logs);
        $this->assertSame(EntityLogActionEnum::CREATE, $logs[0]->getAction()); //order
        $this->assertSame($orderFromDb->getNumber(), $logs[0]->getEntityIdentifier());

        $this->assertSame(EntityLogActionEnum::CREATE, $logs[1]->getAction()); //product
        $this->assertSame($orderFromDb->getProductItems()[0]->getName(), $logs[1]->getEntityIdentifier());

        $this->assertSame(EntityLogActionEnum::CREATE, $logs[2]->getAction()); //transport
        $this->assertSame($orderFromDb->getTransportItem()->getName(), $logs[2]->getEntityIdentifier());

        $this->assertSame(EntityLogActionEnum::CREATE, $logs[3]->getAction()); //payment
        $this->assertSame($orderFromDb->getPaymentItem()->getName(), $logs[3]->getEntityIdentifier());
    }

    public function testRemoveEntity(): void
    {
        $order = $this->getNewOrder();

        $orderFromDb = $this->orderRepository->getById($order->getId());

        $this->assertCount(3, $orderFromDb->getItems());

        $entityId = $orderFromDb->getId();
        $entityName = $this->entityLogFacade->getEntityNameByEntity($orderFromDb);

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
        $entityName = $this->entityLogFacade->getEntityNameByEntity($orderFromDb);

        $expectedOldCity = $orderFromDb->getCity();
        $expectedOldStatusName = $orderFromDb->getStatus()->getName($this->entityLogFacade->getLocaleForEntityLog());
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
        $this->assertSame($newStatus->getName($this->entityLogFacade->getLocaleForEntityLog()), $log->getChangeSet()['status']['newReadableValue']);
    }

    public function testEditCollectionEntity(): void
    {
        $productTicketName = t('100 Czech crowns ticket', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());

        $order = $this->getNewOrder();

        /** @var \App\Model\Order\Order $orderFromDb */
        $orderFromDb = $this->orderRepository->getById($order->getId());

        $entityId = $orderFromDb->getId();
        $entityName = $this->entityLogFacade->getEntityNameByEntity($orderFromDb);

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
        $money = Money::create('2891.74');

        $expectedPrice = $this->priceConverter->convertPriceWithoutVatToDomainDefaultCurrencyPrice(
            $money,
            $this->getReference(CurrencyDataFixture::CURRENCY_CZK),
            Domain::FIRST_DOMAIN_ID,
        );

        $expectedPrice = $expectedPrice->multiply((string)(100 + (float)$expectedVatPercent))->divide(100, 6)->round(2);

        $order = $this->getNewOrder();

        /** @var \App\Model\Order\Order $orderFromDb */
        $orderFromDb = $this->orderRepository->getById($order->getId());

        $entityId = $orderFromDb->getId();
        $entityName = $this->entityLogFacade->getEntityNameByEntity($orderFromDb);

        $orderData = $this->orderDataFactory->createFromOrder($orderFromDb);

        foreach ($orderData->getItemsWithoutTransportAndPayment() as $itemData) {
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

        if ($this->setting->get(PricingSetting::INPUT_PRICE_TYPE) === PricingSetting::PRICE_TYPE_WITHOUT_VAT) {
            $this->assertArrayHasKey('unitPriceWithVat', $changeSet);
            $this->assertSame($expectedPrice->getAmount(), $changeSet['unitPriceWithVat']['newReadableValue']);
        } else {
            $this->assertArrayHasKey('unitPriceWithoutVat', $changeSet);
            $this->assertSame($expectedPrice->getAmount(), $changeSet['unitPriceWithoutVat']['newReadableValue']);
        }

        $this->assertArrayHasKey('vatPercent', $changeSet);
        $this->assertArrayHasKey('quantity', $changeSet);
        $this->assertSame($expectedName, $changeSet['name']['newReadableValue']);
        $this->assertSame($expectedVatPercent, $changeSet['vatPercent']['newReadableValue']);
        $this->assertSame($expectedQuantity, $changeSet['quantity']['newReadableValue']);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    private function getNewOrder(): Order
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);

        $orderInput = $this->orderInputFactory->create($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID));
        $orderInput->addProduct($product, 1);
        $orderInput->setTransport($transport);
        $orderInput->setPayment($payment);

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
        $orderData->currency = $this->getFirstDomainCurrency();

        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );

        return $this->placeOrderFacade->createOrderOnly($orderData);
    }
}
