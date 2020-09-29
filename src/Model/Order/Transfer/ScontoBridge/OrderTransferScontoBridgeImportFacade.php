<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeImportTransfer;
use App\Component\ScontoBridge\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Order\Mail\OrderMailFacade;
use App\Model\Order\OrderRepository;
use App\Model\Order\Status\OrderStatus;
use App\Model\Order\Status\OrderStatusRepository;
use App\Model\Stock\Exception\StockNotFoundException;
use App\Model\Stock\Stock;
use App\Model\Stock\StockRepository;
use DateTime;
use Generator;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Status\Exception\OrderStatusNotFoundException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderTransferScontoBridgeImportFacade extends AbstractScontoBridgeImportTransfer
{
    private const URI_ERP_ORDERS = '/api/services/app/ErpOrder/GetErpOrders';
    private const NEXT_PAGE_POSTFIX = 'NextPage';
    private const ERP_ORDER_STATUS_TYPES = [
        OrderStatus::TYPE_ERP_INSTOCK => 1,
        OrderStatus::TYPE_ERP_INTRANSIT => 4,
        OrderStatus::TYPE_ERP_WAITING => 2,
        OrderStatus::TYPE_ERP_ORDERED => 3,
        OrderStatus::TYPE_ERP_ERROR => 5,
    ];
    public const PAGE_SIZE_LIMIT = 20;

    /**
     * @var Setting
     */
    private Setting $setting;

    /**
     * @var DateTime|null
     */
    private ?DateTime $lastModificationAtFromScontoBridge;

    /**
     * @var ScontoBridgeClient
     */
    private ScontoBridgeClient $scontoBridgeClient;

    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

    /**
     * @var StockRepository
     */
    private StockRepository $stockRepository;

    /**
     * @var OrderStatusRepository
     */
    private OrderStatusRepository $orderStatusRepository;

    /**
     * @var \App\Model\Order\Mail\OrderMailFacade
     */
    private OrderMailFacade $orderMailFacade;

    /**
     * @param \App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Component\ScontoBridge\ScontoBridgeClient $scontoBridgeClient
     * @param \App\Model\Order\OrderRepository $orderRepository
     * @param \App\Model\Stock\StockRepository $stockRepository
     * @param \App\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
     */
    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        Setting $setting,
        ScontoBridgeClient $scontoBridgeClient,
        OrderRepository $orderRepository,
        StockRepository $stockRepository,
        OrderStatusRepository $orderStatusRepository,
        ValidatorInterface $validator,
        OrderMailFacade $orderMailFacade
    ) {
        parent::__construct($scontoBridgeImportTransferDependency);

        $this->setting = $setting;
        $this->lastModificationAtFromScontoBridge = null;
        $this->scontoBridgeClient = $scontoBridgeClient;
        $this->orderRepository = $orderRepository;
        $this->stockRepository = $stockRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->validator = $validator;
        $this->orderMailFacade = $orderMailFacade;
    }

    protected function doBeforeTransfer(): void
    {
        if ($this->lastModificationAtFromScontoBridge === null) {
            $this->lastModificationAtFromScontoBridge =
                new DateTime($this->setting->get(Setting::SCONTO_BRIDGE_TRANSFER_ORDER_STATUS_LAST_UPDATED_DATETIME));
        }

        $this->logger->addInfo(
            sprintf(
                'Importing orders from Sconto bridge from last modification : %s',
                $this->lastModificationAtFromScontoBridge
                    ->modify('+ 1 microseconds')
                    ->format(ScontoBridgeClient::DATE_TIME_FORMAT)
            )
        );
    }

    /**
     * @param array $scontoBridgeOrderData
     */
    protected function processItem(array $scontoBridgeOrderData): void
    {
        $this->validate($scontoBridgeOrderData);
        $orderId = $scontoBridgeOrderData['eshopId'];
        $this->logger->addInfo(sprintf('Downloading date for order id: %d', $orderId));

        try {
            $order = $this->orderRepository->getById($orderId);
            $oldOrderStatus = $order->getStatus();
        } catch (OrderNotFoundException $e) {
            $this->logError($orderId, $e->getMessage());

            return;
        }

        if ($scontoBridgeOrderData['erpOrderNumber'] !== null) {
            $order->setErpNumber($scontoBridgeOrderData['erpOrderNumber']);
        }

        try {
            $status = $this->resolveErpOrderStatus($scontoBridgeOrderData['status']);

            $order->setStatus($status);
        } catch (OrderTransferScontoBridgeImportInvalidStatusException | OrderStatusNotFoundException $e) {
            $this->logError($orderId, $e->getMessage());
        }

        if ($scontoBridgeOrderData['primaryStoreCode'] !== null) {
            try {
                $expeditionStock = $this->findExpeditionStockByCode($scontoBridgeOrderData['primaryStoreCode']);

                $order->setExpeditionStock($expeditionStock);
            } catch (StockNotFoundException $e) {
                $this->logError($orderId, $e->getMessage());
            }
        }

        $this->em->persist($order);
        $this->em->flush();

        if ($oldOrderStatus !== $order->getStatus()) {
            $this->orderMailFacade->sendOrderStatusMailByOrder($order);
        }

        $this->setLastModificationAtFromScontoBridge($scontoBridgeOrderData['erpLastModificationTime']);
    }

    /**
     * @return Generator
     */
    protected function getData(): Generator
    {
        $modifiedAfter = clone $this->lastModificationAtFromScontoBridge;
        $modifiedAfter->modify('+ 1 microseconds');

        $urlParameters = [
            'pageSize' => self::PAGE_SIZE_LIMIT,
            'modifiedAfter' => $modifiedAfter->format(ScontoBridgeClient::DATE_TIME_FORMAT),
        ];

        $nextPageToken = null;
        do {
            $requestUrl = ($nextPageToken === null ? self::URI_ERP_ORDERS : (self::URI_ERP_ORDERS . self::NEXT_PAGE_POSTFIX));
            $requestUrl .= '?' . http_build_query($urlParameters);

            $data = $this->scontoBridgeClient->get($requestUrl);
            $orders = $data['orders']['items'];
            $resultCount = count($orders);
            foreach ($orders as $order) {
                yield $order;
            }

            $nextPageToken = $data['nextPageToken'] ?? null;
            $urlParameters = [
                'nextPageToken' => $nextPageToken,
            ];
        } while ($nextPageToken !== null && $resultCount === self::PAGE_SIZE_LIMIT);
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Importing orders from Sconto bridge finished.');
        $this->setting->set(
            Setting::SCONTO_BRIDGE_TRANSFER_ORDER_STATUS_LAST_UPDATED_DATETIME,
            $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT)
        );
    }

    /**
     * @param string|null $lastModificationAtFromScontoBridge
     */
    private function setLastModificationAtFromScontoBridge(?string $lastModificationAtFromScontoBridge): void
    {
        if ($lastModificationAtFromScontoBridge !== null) {
            $newLastModificationAtFromScontoBridge = new DateTime($lastModificationAtFromScontoBridge);
            if ($this->lastModificationAtFromScontoBridge < $newLastModificationAtFromScontoBridge) {
                $this->lastModificationAtFromScontoBridge = $newLastModificationAtFromScontoBridge;
            }
        }
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('Import objednávek z můstku');
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'OrderImportTransfer';
    }

    /**
     * @param int $erpOrderStatus
     * @return \App\Model\Order\Status\OrderStatus
     */
    private function resolveErpOrderStatus(int $erpOrderStatus): OrderStatus
    {
        $statuses = array_flip(self::ERP_ORDER_STATUS_TYPES);

        if (array_key_exists($erpOrderStatus, $statuses) === false) {
            throw new OrderTransferScontoBridgeImportInvalidStatusException(
                sprintf('Invalid ERP order status: \'%d\'', $erpOrderStatus)
            );
        }

        $statusType = $statuses[$erpOrderStatus];
        $status = $this->orderStatusRepository->findByType($statusType);

        if ($status === null) {
            throw new OrderTransferScontoBridgeImportInvalidStatusException(
                sprintf('Order status type \'%d\' not found', $statusType)
            );
        }

        return $status;
    }

    /**
     * @param string $expeditionStockCode
     * @return Stock
     */
    private function findExpeditionStockByCode(string $expeditionStockCode): Stock
    {
        $stock = $this->stockRepository->findStockByExternalId($expeditionStockCode);

        if ($stock === null) {
            throw new StockNotFoundException(sprintf('Stock with external id %s not found', $expeditionStockCode));
        }

        return $stock;
    }

    /**
     * @param int $orderId
     * @param string $message
     */
    private function logError(int $orderId, string $message): void
    {
        $this->logger->addError(sprintf('Sconto bridge order id \'%d\' import failed: ' . $message, $orderId));
    }

    /**
     * @param array $scontoBridgeOrderData
     */
    private function validate(array $scontoBridgeOrderData): void
    {
        $violations = $this->validator->validate($scontoBridgeOrderData, new Collection([
            'allowExtraFields' => true,
            'fields' => [
                'status' => [
                    new NotBlank()
                ]
            ]
        ]));

        if (count($violations) > 0) {
            throw new TransferInvalidDataAdministratorCriticalException($violations);
        }
    }
}
