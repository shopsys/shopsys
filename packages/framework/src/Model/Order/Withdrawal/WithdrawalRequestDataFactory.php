<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

class WithdrawalRequestDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData
     */
    public function create(): WithdrawalRequestData
    {
        return $this->createInstance();
    }

    /**
     * @param array<string, mixed> $data
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest $withdrawalRequest
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData
     */
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData
     */
    protected function createInstance(): WithdrawalRequestData
    {
        return new WithdrawalRequestData();
    }
}
