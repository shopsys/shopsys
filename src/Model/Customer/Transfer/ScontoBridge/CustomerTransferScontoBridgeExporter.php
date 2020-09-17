<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\AbstractScontoBridgeExporter;
use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Order;

class CustomerTransferScontoBridgeExporter extends AbstractScontoBridgeExporter
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
     * @param Order $order
     */
    public function exportCustomerUser(CustomerUser $customerUser, Order $order): void
    {
        $erpUser = $this->customerTransferScontoBridgeMapper->mapCustomerUserToScontoBridgeCustomerData($customerUser, $order);

        $uri = self::URI_ERP_CUSTOMER;
        $response = $this->scontoBridgeClient->post($uri, $erpUser);
        if ($this->transferFailed($response)) {
            throw $this->createTransferException($response);
        }
    }
}
