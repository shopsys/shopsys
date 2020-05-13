<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeImportTransfer;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Customer\User\CustomerUserFacade;
use DateTime;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;

class ScontoBridgeImportProductStockFacade extends AbstractScontoBridgeImportTransfer
{
    private const URI_ERP_PRODUCT_STOCK = 'services/app/ErpStoreItem/GetErpStoreItems';
    private const URI_ERP_PRODUCT_STOCK_NEXT_PAGE = 'services/app/ErpStoreItem/GetErpStoreItemsNextPage';
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
     * @var \Shopsys\FrameworkBundle\Component\String\HashGenerator
     */
    private $hashGenerator;

    /**
     * @param \App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency
     * @param \App\Component\ScontoBridge\ScontoBridgeClient $scontoBridgeClient
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     */
    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        ScontoBridgeClient $scontoBridgeClient,
        Setting $setting,
        HashGenerator $hashGenerator
    ) {
        parent::__construct($scontoBridgeImportTransferDependency);

        $this->scontoBridgeClient = $scontoBridgeClient;
        $this->setting = $setting;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * @inheritDoc
     */
    protected function doBeforeTransfer(): void
    {
        $this->lastModificationAtFromScontoBridge = $this->setting->get(Setting::SCONTO_BRIDGE_TRANSFER_PRODUCT_STOCK_LAST_UPDATED_DATETIME);
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

        $modifiedAfter = new DateTime('2019-10-23');
        d($modifiedAfter);
        $urlParameters = [
            'pageSize' => self::PAGE_SIZE_LIMIT,
            'modifiedAfter' => $modifiedAfter->format(ScontoBridgeClient::DATE_TIME_FORMAT),
        ];
        $requestUrl = self::URI_ERP_PRODUCT_STOCK . '?' . http_build_query($urlParameters);
        $data = $this->scontoBridgeClient->get($requestUrl);
        d($data);
        exit();
        $nextPageToken = $this->prepareCustomersDataFromApi($data)['nextPageToken'];

        foreach ($this->prepareCustomersDataFromApi($data)['customers'] as $customer) {
            yield $customer;
        }

        while ($nextPageToken !== null) {
            $urlParameters = [
                'nextPageToken' => $nextPageToken,
            ];
            $requestUrl = self::URI_ERP_PRODUCT_STOCK_NEXT_PAGE . '?' . http_build_query($urlParameters);
            $data = $this->scontoBridgeClient->get($requestUrl);

            foreach ($this->prepareCustomersDataFromApi($data)['customers'] as $customer) {
                yield $customer;
            }
            $nextPageToken = $this->prepareCustomersDataFromApi($data)['nextPageToken'];
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareCustomersDataFromApi(array $data): array
    {
        return [
            'customers' => $data['users']['items'],
            'nextPageToken' => $data['nextPageToken'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function processItem(array $scontoBridgeCustomerData): void
    {
        $this->logger->addInfo(sprintf('Processing customer with ERP id : %s', $scontoBridgeCustomerData['erpCustomerNumber']));



        $this->lastModificationAtFromScontoBridge = new DateTime($scontoBridgeCustomerData['modificationTime']) ?? $this->lastModificationAtFromScontoBridge;
    }

    /**
     * @inheritDoc
     */
    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Importing iterable transfer is done.');
        $this->setting->set(Setting::SCONTO_BRIDGE_TRANSFER_PRODUCT_STOCK_LAST_UPDATED_DATETIME, $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT));
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
        $this->lastModificationAtFromScontoBridge = new DateTime($this->setting->get(Setting::SCONTO_BRIDGE_TRANSFER_CUSTOMERS_LAST_UPDATED_DATETIME));
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
