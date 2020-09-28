<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeImportTransfer;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Stock\ProductStockFacade;
use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;

class ScontoBridgeImportProductStockFacade extends AbstractScontoBridgeImportTransfer
{
    private const URI_ERP_PRODUCT_STOCK = 'services/app/ErpStoreItem/GetErpStoreItems';
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
     * @var \App\Model\Product\Transfer\ScontoBridge\ProductStockTransferScontoBridgeValidator
     */
    private $productStockTransferScontoBridgeValidator;

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
     * @param \App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency
     * @param \App\Component\ScontoBridge\ScontoBridgeClient $scontoBridgeClient
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Product\Transfer\ScontoBridge\ProductStockTransferScontoBridgeValidator $productStockTransferScontoBridgeValidator
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler $productExportScheduler
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex $index
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        ScontoBridgeClient $scontoBridgeClient,
        Setting $setting,
        ProductStockTransferScontoBridgeValidator $productStockTransferScontoBridgeValidator,
        ProductStockFacade $productStockFacade,
        ProductExportScheduler $productExportScheduler,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        ProductIndex $index,
        Domain $domain
    ) {
        parent::__construct($scontoBridgeImportTransferDependency);

        $this->scontoBridgeClient = $scontoBridgeClient;
        $this->setting = $setting;
        $this->productStockTransferScontoBridgeValidator = $productStockTransferScontoBridgeValidator;
        $this->productStockFacade = $productStockFacade;
        $this->productExportScheduler = $productExportScheduler;
        $this->indexFacade = $indexFacade;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->index = $index;
        $this->domain = $domain;
    }

    /**
     * @inheritDoc
     */
    protected function doBeforeTransfer(): void
    {
        if ($this->lastModificationAtFromScontoBridge === null) {
            $this->lastModificationAtFromScontoBridge = new DateTime($this->setting->get(Setting::SCONTO_BRIDGE_TRANSFER_PRODUCT_STOCK_LAST_UPDATED_DATETIME));
        }

        $this->logger->addInfo(
            sprintf('Importing productStock data from Sconto bridge from last modification : %s', $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT))
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
            $resultCount = count($data['storeItems']['items']);
            foreach ($data['storeItems']['items'] as $storeItem) {
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

        $this->productStockTransferScontoBridgeValidator->validate($scontoBridgeItemData);

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
            $productStock->setProductQuantity($scontoBridgeItemData['amount']);
            $this->em->flush();
            $this->productExportScheduler->scheduleRowIdForImmediateExport($productStock->getProduct()->getId());
            $this->logger->addInfo(sprintf(
                'ProductStock with product catnum %s and stock ID %s edited',
                $scontoBridgeItemData['sku'],
                $scontoBridgeItemData['storeCode']
            ));
        }

        $this->lastModificationAtFromScontoBridge = $scontoBridgeItemData['modificationTime'] !== null ? new DateTime($scontoBridgeItemData['modificationTime']) : $this->lastModificationAtFromScontoBridge;
    }

    /**
     * @inheritDoc
     */
    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Importing iterable transfer is done.');
        $this->setting->set(Setting::SCONTO_BRIDGE_TRANSFER_PRODUCT_STOCK_LAST_UPDATED_DATETIME, $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT));

        $productIds = $this->productExportScheduler->getRowIdsForImmediateExport();
        foreach ($this->domain->getAllIds() as $domainId) {
            $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition($this->index::getName(), $domainId);
            $this->indexFacade->exportIds($this->index, $indexDefinition, $productIds);
        }
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('Přenos produktových zásob');
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'productStockTransfer';
    }
}
