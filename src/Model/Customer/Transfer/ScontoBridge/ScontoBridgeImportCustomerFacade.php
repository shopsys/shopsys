<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeImportTransfer;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Customer\User\CustomerUserFacade;
use DateTime;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;

class ScontoBridgeImportCustomerFacade extends AbstractScontoBridgeImportTransfer
{
    private const URI_ERP_USER = 'services/app/ErpUser/GetErpUsers';
    private const URI_ERP_USER_NEXT_PAGE = 'services/app/ErpUser/GetErpUsersNextPage';
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
     * @var \App\Model\Customer\Transfer\ScontoBridge\CustomerTransferScontoBridgeValidator
     */
    private $customerTransferScontoBridgeValidator;

    /**
     * @var \App\Model\Customer\Transfer\ScontoBridge\CustomerTransferScontoBridgeMapper
     */
    private $customerTransferScontoBridgeMapper;

    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     */
    private $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\String\HashGenerator
     */
    private $hashGenerator;

    /**
     * @param \App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency
     * @param \App\Component\ScontoBridge\ScontoBridgeClient $scontoBridgeClient
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Customer\Transfer\ScontoBridge\CustomerTransferScontoBridgeValidator $customerTransferScontoBridgeValidator
     * @param \App\Model\Customer\Transfer\ScontoBridge\CustomerTransferScontoBridgeMapper $customerTransferScontoBridgeMapper
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     */
    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        ScontoBridgeClient $scontoBridgeClient,
        Setting $setting,
        CustomerTransferScontoBridgeValidator $customerTransferScontoBridgeValidator,
        CustomerTransferScontoBridgeMapper $customerTransferScontoBridgeMapper,
        CustomerUserFacade $customerUserFacade,
        HashGenerator $hashGenerator
    ) {
        parent::__construct($scontoBridgeImportTransferDependency);

        $this->scontoBridgeClient = $scontoBridgeClient;
        $this->setting = $setting;
        $this->customerTransferScontoBridgeValidator = $customerTransferScontoBridgeValidator;
        $this->customerTransferScontoBridgeMapper = $customerTransferScontoBridgeMapper;
        $this->customerUserFacade = $customerUserFacade;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * @inheritDoc
     */
    protected function doBeforeTransfer(): void
    {
        $this->lastModificationAtFromScontoBridge = new DateTime($this->setting->get(Setting::SCONTO_BRIDGE_TRANSFER_CUSTOMERS_LAST_UPDATED_DATETIME));
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
        $requestUrl = self::URI_ERP_USER . '?' . http_build_query($urlParameters);
        $data = $this->scontoBridgeClient->get($requestUrl);
        $nextPageToken = $this->prepareCustomersDataFromApi($data)['nextPageToken'];

        foreach ($this->prepareCustomersDataFromApi($data)['customers'] as $customer) {
            yield $customer;
        }

        while ($nextPageToken !== null) {
            $urlParameters = [
                'nextPageToken' => $nextPageToken,
            ];
            $requestUrl = self::URI_ERP_USER_NEXT_PAGE . '?' . http_build_query($urlParameters);
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
        $this->logger->info(sprintf('Processing customer with ERP id : %s', $scontoBridgeCustomerData['erpCustomerNumber']));

        $this->customerTransferScontoBridgeValidator->validate($scontoBridgeCustomerData);

        $customerUser = $this->customerUserFacade->findByErpCustomerNumber($scontoBridgeCustomerData['erpCustomerNumber']);
        $customerUserUpdateData = $this->customerTransferScontoBridgeMapper->mapScontoBridgeCustomerDataToCustomerUserUpdateData(
            $scontoBridgeCustomerData,
            $customerUser
        );

        if ($customerUser === null) {
            $customerUserUpdateData->customerUserData->password = $this->hashGenerator->generateHash(CustomerUserPasswordFacade::RESET_PASSWORD_HASH_LENGTH);
            $newCustomerUser = $this->customerUserFacade->create($customerUserUpdateData);
            $this->logger->addInfo(sprintf('Created customer with eshop ID: %s ', $newCustomerUser->getId()));
        } else {
            $this->customerUserFacade->editByAdmin($customerUser->getId(), $customerUserUpdateData);
            $this->em->flush();
            $this->logger->addInfo(sprintf('Updated customer with eshop ID: %s', $customerUser->getId()));
        }

        $this->lastModificationAtFromScontoBridge = new DateTime($scontoBridgeCustomerData['modificationTime']) ?? $this->lastModificationAtFromScontoBridge;
    }

    /**
     * @inheritDoc
     */
    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Importing iterable transfer is done.');
        $this->setting->set(Setting::SCONTO_BRIDGE_TRANSFER_CUSTOMERS_LAST_UPDATED_DATETIME, $this->lastModificationAtFromScontoBridge->format(ScontoBridgeClient::DATE_TIME_FORMAT));
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
}
