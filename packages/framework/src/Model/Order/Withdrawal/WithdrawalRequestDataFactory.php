<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

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
        $withdrawalRequestData->telephone = $data['telephone'] ?? null;
        $withdrawalRequestData->note = $data['note'] ?? null;

        return $withdrawalRequestData;
    }

    public function createFromWithdrawalRequest(WithdrawalRequest $withdrawalRequest): WithdrawalRequestData
    {
        $withdrawalRequestData = $this->createInstance();

        $withdrawalRequestData->firstName = $withdrawalRequest->getFirstName();
        $withdrawalRequestData->lastName = $withdrawalRequest->getLastName();
        $withdrawalRequestData->email = $withdrawalRequest->getEmail();
        $withdrawalRequestData->telephone = $withdrawalRequest->getTelephone();
        $withdrawalRequestData->note = $withdrawalRequest->getNote();
        $withdrawalRequestData->requestedAt = $withdrawalRequest->getRequestedAt();

        return $withdrawalRequestData;
    }

    protected function createInstance(): WithdrawalRequestData
    {
        return new WithdrawalRequestData();
    }
}
