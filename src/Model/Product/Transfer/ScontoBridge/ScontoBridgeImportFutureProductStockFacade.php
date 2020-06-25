<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeImportTransfer;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Stock\ProductStockFacade;
use DateTime;

class ScontoBridgeImportFutureProductStockFacade extends AbstractScontoBridgeImportTransfer
{
    private const URI_ERP_PRODUCT_STOCK = 'services/app/ErpStoreOrderItem/GetErpStoreOrderItems';
    private const NEXT_PAGE_POSTFIX = 'NextPage';
    public const PAGE_SIZE_LIMIT = 20;

    /**
     * @var \App\Component\ScontoBridge\ScontoBridgeClient
     */
    private $scontoBridgeClient;

    /**
     * @var \DateTime|null
     */
    private $lastModificationAtFromScontoBridge;

    /**
     * @var \App\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \App\Model\Product\Transfer\ScontoBridge\FutureProductStockTransferScontoBridgeValidator
     */
    private $futureProductStockTransferScontoBridgeValidator;

    /**
     * @var \App\Model\Stock\ProductStockFacade
     */
    private $productStockFacade;

    /**
     * @param \App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency
     * @param \App\Component\ScontoBridge\ScontoBridgeClient $scontoBridgeClient
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Product\Transfer\ScontoBridge\FutureProductStockTransferScontoBridgeValidator $futureProductStockTransferScontoBridgeValidator
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     */
    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        ScontoBridgeClient $scontoBridgeClient,
        Setting $setting,
        FutureProductStockTransferScontoBridgeValidator $futureProductStockTransferScontoBridgeValidator,
        ProductStockFacade $productStockFacade
    ) {
        parent::__construct($scontoBridgeImportTransferDependency);

        $this->scontoBridgeClient = $scontoBridgeClient;
        $this->setting = $setting;
        $this->futureProductStockTransferScontoBridgeValidator = $futureProductStockTransferScontoBridgeValidator;
        $this->productStockFacade = $productStockFacade;
    }

    /**
     * @inheritDoc
     */
    protected function doBeforeTransfer(): void
    {
        $this->productStockFacade->resetFutureProductStockAfterNowDate();

        if ($this->lastModificationAtFromScontoBridge === null) {
            $this->lastModificationAtFromScontoBridge = new DateTime($this->setting->get(Setting::SCONTO_BRIDGE_TRANSFER_FUTURE_PRODUCT_STOCK_LAST_UPDATED_DATETIME));
        }

        $this->logger->addInfo(
            sprintf('Importing future productStock data from Sconto bridge from last modification : %s', $this->lastModificationAtFromScontoBridge->modify('+ 1 microseconds')->format(ScontoBridgeClient::DATE_TIME_FORMAT))
        );
    }

    /**
     * @inheritDoc
     */
    protected function getData(): \Generator
    {
        $modifiedAfter = clone $this->lastModificationAtFromScontoBridge;
        $modifiedAfter->modify('+ 1 microseconds');

        $urlParameters = [
            'pageSize' => self::PAGE_SIZE_LIMIT,
            'modifiedAfter' => $modifiedAfter->format(ScontoBridgeClient::DATE_TIME_FORMAT),
        ];

        $nextPageToken = null;
        do {
            $requestUrl = ($nextPageToken === null ? self::URI_ERP_PRODUCT_STOCK : (self::URI_ERP_PRODUCT_STOCK . self::NEXT_PAGE_POSTFIX));
            $requestUrl .= '?' . http_build_query($urlParameters);

            $data = $this->scontoBridgeClient->get($requestUrl);
            $resultCount = count($data['storeOrderItems']['items']);
            foreach ($data['storeOrderItems']['items'] as $storeItem) {
                yield $storeItem;
            }

            $nextPageToken = $data['nextPageToken'] ?? null;
            $urlParameters = [
                'nextPageToken' => $nextPageToken,
            ];
        } while ($nextPageToken !== null && $resultCount === self::PAGE_SIZE_LIMIT);
    }

    /**
     * @inheritDoc
     */
    protected function processItem(array $scontoBridgeItemData): void
    {
        $this->logger->addInfo(sprintf('Processing store item with ERP id(SKU) : %s', $scontoBridgeItemData['sku']));

        $this->futureProductStockTransferScontoBridgeValidator->validate($scontoBridgeItemData);

        $productStock = $this->productStockFacade->findProductStockByProductCatnumAndStockExternalId(
            $scontoBridgeItemData['sku'],
            $scontoBridgeItemData['storeCode']
        );

        if ($productStock === null) {
            $this->logger->addWarning(sprintf(
                'ProductStock with product catnum %s and stock ID %s not found',
                $scontoBridgeItemData['sku'],
                $scontoBridgeItemData['storeCode']
            ));
        } else {
            if ($this->buildDate($scontoBridgeItemData['dateArrival']) !== null) {
                $productStock->setFutureProductQuantity(null);
                $productStock->setDateOfStorage(null);
            } else {
                $futureDateOfStorage = $this->buildDate($scontoBridgeItemData['dateConfirmedArrival']);
                if ($futureDateOfStorage === null) {
                    $futureDateOfStorage = $this->buildDate($scontoBridgeItemData['dateExpectedArrival']);
                }

                if ($futureDateOfStorage < (new DateTime())) {
                    $productStock->setFutureProductQuantity(null);
                    $productStock->setDateOfStorage(null);
                } else {
                    $productStock->setFutureProductQuantity($scontoBridgeItemData['amount']);
                    $productStock->setDateOfStorage($futureDateOfStorage);
                }
            }

            $this->em->flush();
            $this->logger->addInfo(sprintf(
                'ProductStock with product catnum %s and stock ID %s edited',
                $scontoBridgeItemData['sku'],
                $scontoBridgeItemData['storeCode']
            ));
        }

        $this->lastModificationAtFromScontoBridge = $scontoBridgeItemData['modificationTime'] !== null ? new DateTime($scontoBridgeItemData['modificationTime']) : $this->lastModificationAtFromScontoBridge;
    }

    /**
     * @param array|null $erpDate
     * @return \DateTime|null
     */
    private function buildDate(?array $erpDate): ?DateTime
    {
        if ($erpDate === null) {
            return null;
        }

        $dateTime = new DateTime();
        if ($erpDate['month'] === 0 && $erpDate['day'] === 0) {
            $dateTime->setISODate($erpDate['year'], $erpDate['week'], 7);
        } else {
            $dateTime->setDate($erpDate['year'], $erpDate['month'], $erpDate['day']);
        }

        return $dateTime;
    }

    /**
     * @inheritDoc
     */
    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Importing iterable transfer is done.');
        $this->setting->set(Setting::SCONTO_BRIDGE_TRANSFER_FUTURE_PRODUCT_STOCK_LAST_UPDATED_DATETIME, $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT));
    }

    /**
     * @inheritDoc
     */
    public function cronSleep(): void
    {
        $this->logger->addInfo(
            sprintf('Sleeping cron for last modified : %s', $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT))
        );
    }

    /**
     * @inheritDoc
     */
    public function cronWakeUp(): void
    {
        $this->lastModificationAtFromScontoBridge = new DateTime($this->setting->get(Setting::SCONTO_BRIDGE_TRANSFER_FUTURE_PRODUCT_STOCK_LAST_UPDATED_DATETIME));
        $this->logger->addInfo(
            sprintf('Wake up cron for last modified : %s', $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT))
        );
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('Přenos budoucích produktových zásob');
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'futureProductStockTransfer';
    }
}
