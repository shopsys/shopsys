<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeImportTransfer;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Component\Setting\Setting;
use DateTime;

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
     * @param \App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency
     * @param \App\Component\ScontoBridge\ScontoBridgeClient $scontoBridgeClient
     * @param \App\Component\Setting\Setting $setting
     */
    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        ScontoBridgeClient $scontoBridgeClient,
        Setting $setting
    ) {
        parent::__construct($scontoBridgeImportTransferDependency);

        $this->scontoBridgeClient = $scontoBridgeClient;
        $this->setting = $setting;
    }

    /**
     * @inheritDoc
     */
    protected function doBeforeTransfer(): void
    {
        if ($this->lastModificationAtFromScontoBridge === null) {
            $this->lastModificationAtFromScontoBridge = $this->setting->get(Setting::SCONTO_BRIDGE_TRANSFER_PRODUCT_STOCK_LAST_UPDATED_DATETIME);
        }

        $this->logger->addInfo(
            sprintf('Importing customers data from Sconto bridge from last modification : %s', $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT))
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

        //TODO implement code here
//        $scontoBridgeData['sku'];
//        $scontoBridgeData['storeCode'];
//        $scontoBridgeData['amount'];
//        $scontoBridgeData['inShowroom'];
//        $scontoBridgeData['modificationTime'];
//        $scontoBridgeData['dateArrival']['year'];
//        $scontoBridgeData['dateArrival']['month'];
//        $scontoBridgeData['dateArrival']['day'];
//        $scontoBridgeData['dateArrival']['week'];

        $this->lastModificationAtFromScontoBridge = $scontoBridgeItemData['modificationTime'] !== null ? new DateTime($scontoBridgeItemData['modificationTime']) : $this->lastModificationAtFromScontoBridge;
    }

    /**
     * @inheritDoc
     */
    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Importing iterable transfer is done.');
        //$this->setting->set(Setting::SCONTO_BRIDGE_TRANSFER_PRODUCT_STOCK_LAST_UPDATED_DATETIME, $this->lastModificationAtFromScontoBridge);
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
        $this->lastModificationAtFromScontoBridge = $this->setting->get(Setting::SCONTO_BRIDGE_TRANSFER_PRODUCT_STOCK_LAST_UPDATED_DATETIME);
        $this->logger->addInfo(
            sprintf('Wake up cron for last modified : %s', $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT))
        );
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
