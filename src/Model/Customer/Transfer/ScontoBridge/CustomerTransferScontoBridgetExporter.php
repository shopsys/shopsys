<?php
declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeExporter;
use App\Model\Customer\User\CustomerUser;

class CustomerTransferScontoBridgetExporter extends AbstractScontoBridgeExporter
{
    private const URI_ERP_CUSTOMER = '/api/services/app/ErpUser/SaveErpUser';

    /**
     * @var CustomerTransferScontoBridgeMapper
     */
    private CustomerTransferScontoBridgeMapper $customerTransferScontoBridgeMapper;

    /**
     * @var ScontoBridgeClient
     */
    private ScontoBridgeClient $scontoBridgeClient;

    /**
     * CustomerTransferScontoBridgetExporter constructor.
     * @param CustomerTransferScontoBridgeMapper $customerTransferScontoBridgeMapper
     * @param ScontoBridgeClient $scontoBridgeClient
     */
    public function __construct(
        CustomerTransferScontoBridgeMapper $customerTransferScontoBridgeMapper,
        ScontoBridgeClient $scontoBridgeClient
    ) {
        $this->customerTransferScontoBridgeMapper = $customerTransferScontoBridgeMapper;
        $this->scontoBridgeClient = $scontoBridgeClient;
    }

    /**
     * @param CustomerUser $customerUser
     */
    public function exportCustomerUser(CustomerUser $customerUser): void
    {
        $erpUser = $this->customerTransferScontoBridgeMapper->mapCustomerUserToScontoBridgeCustomerData($customerUser);

        $uri = self::URI_ERP_CUSTOMER;
        $response = $this->scontoBridgeClient->post($uri, $erpUser);
        if ($this->transferFailed($response)) {
            throw $this->createTransferException($response);
        }
    }
}
