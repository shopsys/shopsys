<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeImportTransfer;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Stock\Future\FutureProductStockData;
use App\Model\Stock\Future\FutureProductStockDataFactory;
use App\Model\Stock\Future\FutureProductStockFacade;
use App\Model\Stock\ProductStockFacade;
use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;

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
     * @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler
     */
    private $productExportScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade
     */
    private $indexFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     */
    private $indexDefinitionLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex
     */
    private $index;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Stock\Future\FutureProductStockFacade
     */
    private FutureProductStockFacade $futureProductStockFacade;

    /**
     * @var \App\Model\Stock\Future\FutureProductStockDataFactory
     */
    private FutureProductStockDataFactory $futureProductStockDataFactory;

    /**
     * @var string[][]
     */
    private array $importedStoreCodesBySku = [];

    /**
     * @param \App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency
     * @param \App\Component\ScontoBridge\ScontoBridgeClient $scontoBridgeClient
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Product\Transfer\ScontoBridge\FutureProductStockTransferScontoBridgeValidator $futureProductStockTransferScontoBridgeValidator
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler $productExportScheduler
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex $index
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Stock\Future\FutureProductStockFacade $futureProductStockFacade
     * @param \App\Model\Stock\Future\FutureProductStockDataFactory $futureProductStockDataFactory
     */
    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        ScontoBridgeClient $scontoBridgeClient,
        Setting $setting,
        FutureProductStockTransferScontoBridgeValidator $futureProductStockTransferScontoBridgeValidator,
        ProductStockFacade $productStockFacade,
        ProductExportScheduler $productExportScheduler,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        ProductIndex $index,
        Domain $domain,
        FutureProductStockFacade $futureProductStockFacade,
        FutureProductStockDataFactory $futureProductStockDataFactory
    ) {
        parent::__construct($scontoBridgeImportTransferDependency);

        $this->scontoBridgeClient = $scontoBridgeClient;
        $this->setting = $setting;
        $this->futureProductStockTransferScontoBridgeValidator = $futureProductStockTransferScontoBridgeValidator;
        $this->productStockFacade = $productStockFacade;
        $this->productExportScheduler = $productExportScheduler;
        $this->indexFacade = $indexFacade;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->index = $index;
        $this->domain = $domain;
        $this->futureProductStockFacade = $futureProductStockFacade;
        $this->futureProductStockDataFactory = $futureProductStockDataFactory;
    }

    /**
     * @inheritDoc
     */
    protected function doBeforeTransfer(): void
    {
        $this->productStockFacade->resetFutureProductStockAfterNowDate();
        $this->futureProductStockFacade->cleanOrdersAfterDeadline();

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

        $erpId = (string)$scontoBridgeItemData['erpId'];
        $futureProductStock = $this->futureProductStockFacade->findByErpId($erpId);

        if ($futureProductStock === null) {
            if ($scontoBridgeItemData['isLate'] === false) {
                $futureProductStockData = $this->futureProductStockDataFactory->create();
                $futureProductStockData = $this->mapScontoBridgeItemDataToFutureProductStorageData($scontoBridgeItemData, $futureProductStockData);
                $futureProductStock = $this->futureProductStockFacade->create($futureProductStockData);
            }
        } else {
            $futureProductStockData = $this->futureProductStockDataFactory->createFromFutureProductStock($futureProductStock);
            $futureProductStockData = $this->mapScontoBridgeItemDataToFutureProductStorageData($scontoBridgeItemData, $futureProductStockData);
            $futureProductStock = $this->futureProductStockFacade->edit($erpId, $futureProductStockData);
        }

        if ($futureProductStock !== null) {
            $this->importedStoreCodesBySku[$futureProductStock->getSku()][$futureProductStock->getStoreCode()] = $futureProductStock->getStoreCode();
        }
        $this->lastModificationAtFromScontoBridge = $scontoBridgeItemData['modificationTime'] !== null ? new DateTime($scontoBridgeItemData['modificationTime']) : $this->lastModificationAtFromScontoBridge;
    }

    /**
     * @param array $scontoBridgeItemData
     * @param \App\Model\Stock\Future\FutureProductStockData $futureProductStockData
     * @return \App\Model\Stock\Future\FutureProductStockData
     */
    private function mapScontoBridgeItemDataToFutureProductStorageData(array $scontoBridgeItemData, FutureProductStockData $futureProductStockData): FutureProductStockData
    {
        $futureProductStockData->erpId = $scontoBridgeItemData['erpId'];
        $futureProductStockData->sku = $scontoBridgeItemData['sku'];
        $futureProductStockData->storeCode = $scontoBridgeItemData['storeCode'];
        $futureProductStockData->amount = $scontoBridgeItemData['amount'];
        $futureProductStockData->dateExpectedArrival = $this->buildDate($scontoBridgeItemData['dateExpectedArrival']);
        $futureProductStockData->dateConfirmedArrival = $this->buildDate($scontoBridgeItemData['dateConfirmedArrival']);
        $futureProductStockData->isLate = $scontoBridgeItemData['isLate'];
        if ($scontoBridgeItemData['dateArrival'] !== null) {
            $futureProductStockData->isLate = true;
        }

        return $futureProductStockData;
    }

    /**
     * @param string|null $erpDate
     * @return \DateTime|null
     */
    private function buildDate(?string $erpDate): ?DateTime
    {
        if ($erpDate === null) {
            return null;
        }

        return new DateTime($erpDate);
    }

    /**
     * @inheritDoc
     */
    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Importing iterable transfer is done.');
        $this->setting->set(Setting::SCONTO_BRIDGE_TRANSFER_FUTURE_PRODUCT_STOCK_LAST_UPDATED_DATETIME, $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT));

        foreach ($this->importedStoreCodesBySku as $sku => $storeCodes) {
            foreach ($storeCodes as $storeCode) {
                $this->calculateClosestFutureProductStockBySkuAndStoreCode((string)$sku, (string)$storeCode);
            }
        }

        $productIds = $this->productExportScheduler->getRowIdsForImmediateExport();
        foreach ($this->domain->getAllIds() as $domainId) {
            $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition($this->index::getName(), $domainId);
            $this->indexFacade->exportIds($this->index, $indexDefinition, $productIds);
        }
    }

    /**
     * @param string $sku
     * @param string $storeCode
     */
    private function calculateClosestFutureProductStockBySkuAndStoreCode(string $sku, string $storeCode): void
    {
        $productStock = $this->productStockFacade->findProductStockByProductCatnumAndStockExternalId(
            $sku,
            $storeCode
        );

        if ($productStock === null) {
            $this->logger->addWarning(sprintf(
                'ProductStock with product catnum %s and stock ID %s not found',
                $sku,
                $storeCode
            ));
        } else {
            $futureProductStock = $this->futureProductStockFacade->findClosestFutureProductStockBySkuAndStoreCode($sku, $storeCode);
            if ($futureProductStock === null) {
                $productStock->setFutureProductQuantity(null);
                $productStock->setDateOfStorage(null);
            } else {
                $futureDateOfStorage = $futureProductStock->getDateConfirmedArrival();
                if ($futureDateOfStorage === null) {
                    $futureDateOfStorage = $futureProductStock->getDateExpectedArrival();
                }

                if ($futureDateOfStorage < (new DateTime())->setTime(0, 0)) {
                    $productStock->setFutureProductQuantity(null);
                    $productStock->setDateOfStorage(null);
                } else {
                    $productStock->setFutureProductQuantity($futureProductStock->getAmount());
                    $productStock->setDateOfStorage($futureDateOfStorage);
                }
            }

            $this->em->flush();
            $this->productExportScheduler->scheduleRowIdForImmediateExport($productStock->getProduct()->getId());
            $futureDate = $productStock->getDateOfStorage() !== null ? $productStock->getDateOfStorage()->format('Y-m-d') : 'null';
            $futureProductQuantity = $productStock->getFutureProductQuantity() !== null ? $productStock->getFutureProductQuantity() : null;
            $this->logger->addInfo(sprintf(
                'ProductStock with product catnum %s and stock ID %s edited, (date: %s, futureQuantity: %d)',
                $sku,
                $storeCode,
                $futureDate,
                $futureProductQuantity
            ));
        }
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
