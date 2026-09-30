<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;

class WithdrawalRequestDataFactory
{
    public function create(): WithdrawalRequestData
    {
        return $this->createInstance();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromArray(array $data): WithdrawalRequestData
    {
        $withdrawalRequestData = $this->createInstance();

        $withdrawalRequestData->firstName = $data['firstName'];
        $withdrawalRequestData->lastName = $data['lastName'];
        $withdrawalRequestData->email = $data['email'];
        $withdrawalRequestData->telephone = isset($data['telephone']) ? PhoneData::fromArray($data['telephone']) : null;
        $withdrawalRequestData->note = $data['note'] ?? null;

        return $withdrawalRequestData;
    }

    public function createFromWithdrawalRequest(WithdrawalRequest $withdrawalRequest): WithdrawalRequestData
    {
        $withdrawalRequestData = $this->createInstance();

        $withdrawalRequestData->firstName = $withdrawalRequest->getFirstName();
        $withdrawalRequestData->lastName = $withdrawalRequest->getLastName();
        $withdrawalRequestData->email = $withdrawalRequest->getEmail();
        $withdrawalRequestData->telephone = $withdrawalRequest->getTelephoneData();
        $withdrawalRequestData->note = $withdrawalRequest->getNote();
        $withdrawalRequestData->requestedAt = $withdrawalRequest->getRequestedAt();
        $withdrawalRequestData->confirmed = $withdrawalRequest->isConfirmed();
        $withdrawalRequestData->confirmationHash = $withdrawalRequest->getConfirmationHash();

        return $withdrawalRequestData;
    }

    public function createFromWithdrawalRequestOrPrefilledFromOrder(
        Order $order,
        ?WithdrawalRequest $withdrawalRequest,
    ): WithdrawalRequestData {
        if ($withdrawalRequest !== null) {
            return $this->createFromWithdrawalRequest($withdrawalRequest);
        }

        $withdrawalRequestData = $this->createInstance();
        $withdrawalRequestData->firstName = $order->getFirstName();
        $withdrawalRequestData->lastName = $order->getLastName();
        $withdrawalRequestData->email = $order->getEmail();
        $withdrawalRequestData->telephone = $order->getTelephoneData();

        return $withdrawalRequestData;
    }

    protected function createInstance(): WithdrawalRequestData
    {
        return new WithdrawalRequestData();
    }
}
