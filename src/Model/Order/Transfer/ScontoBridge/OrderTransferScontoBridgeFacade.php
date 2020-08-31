<?php
declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Model\Customer\Transfer\ScontoBridge\CustomerTransferScontoBridgeMapper;
use App\Model\Order\Order;
use App\Model\Order\OrderDataFactory;
use App\Model\Order\OrderRepository;
use App\Model\Order\OrderScontoBridgeStatusEnum;
use App\Model\Transfer\TransferIdentificationInterface;
use App\Model\Transfer\TransferLoggerInterface;

//use App\Model\Order\Transfer\ScontoBridge\Mapper\ScontoBridgeErpOrderMapper;

class OrderTransferScontoBridgeFacade implements TransferIdentificationInterface
{
    private const URI_ERP_ORDER = '/api/services/app/ErpOrder/SaveErpOrder';
    private const URI_ERP_CUSTOMER = '/api/services/app/ErpUser/SaveErpUser';

    /**
     * @var ScontoBridgeImportTransferDependency
     */
    private ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency;

    /**
     * @var ScontoBridgeClient
     */
    private ScontoBridgeClient $scontoBridgeClient;

    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

    /**
     * @var CustomerTransferScontoBridgeMapper
     */
    private CustomerTransferScontoBridgeMapper $customerTransferScontoBridgeMapper;

    /**
     * @var OrderTransferScontoBridgeMapper
     */
    private OrderTransferScontoBridgeMapper $orderTransferScontoBridgeMapper;

    /**
     * @var \App\Model\Transfer\TransferLoggerInterface
     */
    private TransferLoggerInterface $logger;

    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        ScontoBridgeClient $scontoBridgeClient,
        OrderRepository $orderRepository,
        CustomerTransferScontoBridgeMapper $customerTransferScontoBridgeMapper,
        OrderTransferScontoBridgeMapper $orderTransferScontoBridgeMapper
    ) {
        $this->scontoBridgeImportTransferDependency = $scontoBridgeImportTransferDependency;
        $this->scontoBridgeClient = $scontoBridgeClient;
        $this->orderRepository = $orderRepository;
        $this->customerTransferScontoBridgeMapper = $customerTransferScontoBridgeMapper;
        $this->scontoBridgeErpOrderMapper = $orderTransferScontoBridgeMapper;
        $this->logger = $this->scontoBridgeImportTransferDependency->getTransferLoggerFactory()->getTransferLoggerByIdentifier($this);
        $this->orderDataFactory = $orderDataFactory;
    }

    public function runTransfer()
    {
        foreach ($this->getData() as $order) {
            //$this->markOrderScontoBridgeStatusProcessing($order);
//            try {
                $this->processItem($order);
//            } catch (\Exception $e) { //fixme
                //$this->logger->addCritical($e->getMessage());
                //$this->markOrderScontoBridgeStatusError($order);
//            }
            //$this->markOrderScontoBridgeStatusDone($order);
        }
    }

    protected function processItem(Order $order): void
    {
        $customerUser = $order->getCustomerUser();
        if ($customerUser !== null) {
            $user = $this->customerTransferScontoBridgeMapper->mapCustomerUserToScontoBridgeCustomerData($customerUser);

            $uri = self::URI_ERP_CUSTOMER;
            $response = $this->scontoBridgeClient->post($uri, $user);
        }

//        $erpOrder = $this->orderTransferScontoBridgeMapper->mapOrderToScontoBridgeOrderData($order);
//        $uri = self::URI_ERP_ORDER;
//        $response = $this->scontoBridgeClient->post($uri, $erpOrder);
    }

    protected function getData(): \Generator
    {
        foreach ($this->orderRepository->getAllOrdersNotSentToScontoBridge() as $order) {
            yield $order;
        }
    }

    private function markOrderScontoBridgeStatusProcessing(Order $order): void
    {
        $this->setOrderScontoBridgeStatus(
            $order,
            OrderScontoBridgeStatusEnum::create(OrderScontoBridgeStatusEnum::STATUS_PROCESSING)
        );
    }

    private function markOrderScontoBridgeStatusDone(Order $order): void
    {
        $this->setOrderScontoBridgeStatus(
            $order,
            OrderScontoBridgeStatusEnum::create(OrderScontoBridgeStatusEnum::STATUS_DONE)
        );
    }

    private function markOrderScontoBridgeStatusError($order)
    {
        $this->setOrderScontoBridgeStatus(
            $order,
            OrderScontoBridgeStatusEnum::create(OrderScontoBridgeStatusEnum::STATUS_ERROR)
        );
    }

    private function setOrderScontoBridgeStatus(Order $order, OrderScontoBridgeStatusEnum $status): void
    {
        $order->setScontoBridgeStatus($status);
        $em = $this->scontoBridgeImportTransferDependency->getEm();
        $em->persist($order);
        $em->flush();
    }

    public function getTransferName(): string
    {
        return t('Export objednávek do můstku');
    }

    public function getTransferIdentifier(): string
    {
        return 'ProductExportTransfer';
    }

    public function getServiceIdentifier(): string
    {
        return 'ScontoBridge';
    }
}
