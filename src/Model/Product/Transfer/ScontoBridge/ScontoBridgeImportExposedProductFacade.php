<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeImportTransfer;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Stock\ProductStockFacade;
use DateTime;

class ScontoBridgeImportExposedProductFacade extends AbstractScontoBridgeImportTransfer
{
    private const URI_ERP_PRODUCT_STOCK = 'services/app/ErpShowroomItem/GetErpShowroomItems';
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
     * @var \App\Model\Stock\ProductStockFacade
     */
    private $productStockFacade;

    /**
     * @var \App\Model\Product\Transfer\ScontoBridge\ExposedProductTransferScontoBridgeValidator
     */
    private ExposedProductTransferScontoBridgeValidator $exposedProductTransferScontoBridgeValidator;

    /**
     * @param \App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency
     * @param \App\Component\ScontoBridge\ScontoBridgeClient $scontoBridgeClient
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Product\Transfer\ScontoBridge\ExposedProductTransferScontoBridgeValidator $exposedProductTransferScontoBridgeValidator
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     */
    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        ScontoBridgeClient $scontoBridgeClient,
        Setting $setting,
        ExposedProductTransferScontoBridgeValidator $exposedProductTransferScontoBridgeValidator,
        ProductStockFacade $productStockFacade
    ) {
        parent::__construct($scontoBridgeImportTransferDependency);

        $this->scontoBridgeClient = $scontoBridgeClient;
        $this->setting = $setting;
        $this->productStockFacade = $productStockFacade;
        $this->exposedProductTransferScontoBridgeValidator = $exposedProductTransferScontoBridgeValidator;
    }

    /**
     * @inheritDoc
     */
    protected function doBeforeTransfer(): void
    {
        if ($this->lastModificationAtFromScontoBridge === null) {
            $this->lastModificationAtFromScontoBridge = new DateTime($this->setting->get(Setting::SCONTO_BRIDGE_TRANSFER_EXPOSED_PRODUCT_LAST_UPDATED_DATETIME));
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
            $resultCount = count($data['showroomItems']['items']);
            foreach ($data['showroomItems']['items'] as $storeItem) {
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

        $this->exposedProductTransferScontoBridgeValidator->validate($scontoBridgeItemData);

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
            $productStock->setProductExposed($scontoBridgeItemData['inShowroom']);
            $this->em->flush();
            $this->logger->addInfo(sprintf(
                'ProductStock with product catnum %s and stock ID %s edited',
                $scontoBridgeItemData['sku'],
                $scontoBridgeItemData['storeCode']
            ));
        }

        $this->setLastModificationAtFromScontoBridge($scontoBridgeItemData['modificationTime']);
    }

    /**
     * @inheritDoc
     */
    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Importing iterable transfer is done.');
        $this->setting->set(Setting::SCONTO_BRIDGE_TRANSFER_EXPOSED_PRODUCT_LAST_UPDATED_DATETIME, $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT));
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
        return t('Přenos vystavených produktů');
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'exposedProductTransfer';
    }
}
