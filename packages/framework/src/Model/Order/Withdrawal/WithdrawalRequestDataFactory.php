<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

class WithdrawalRequestDataFactory
{
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
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData
     */
    protected function createInstance(): WithdrawalRequestData
    {
        return new WithdrawalRequestData();
    }
}
