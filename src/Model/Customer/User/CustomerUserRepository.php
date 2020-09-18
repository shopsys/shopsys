<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository as BaseCustomerUserRepository;

/**
 * @method \App\Model\Customer\User\CustomerUser|null findCustomerUserByEmailAndDomain(string $email, int $domainId)
 * @method \App\Model\Customer\User\CustomerUser|null getCustomerUserByEmailAndDomain(string $email, int $domainId)
 * @method \App\Model\Customer\User\CustomerUser getCustomerUserById(int $id)
 * @method \App\Model\Customer\User\CustomerUser|null findById(int $id)
 * @method \App\Model\Customer\User\CustomerUser|null findByIdAndLoginToken(int $id, string $loginToken)
 * @method \App\Model\Customer\User\CustomerUser getOneByUuid(string $uuid)
 */
class CustomerUserRepository extends BaseCustomerUserRepository
{
    /**
     * @param int $erpCustomerNumber
     * @return \App\Model\Customer\User\CustomerUser|null
     */
    public function findByErpCustomerNumber(int $erpCustomerNumber): ?CustomerUser
    {
        return $this->getCustomerUserRepository()->findOneBy([
            'erpCustomerNumber' => $erpCustomerNumber,
        ]);
    }

    /**
     * @param CustomerUserScontoBridgeStatusEnum ...$statuses
     * @return CustomerUser[]
     */
    public function findByScontoBridgeStatuses(CustomerUserScontoBridgeStatusEnum ...$statuses): array
    {
        $statusStrings = array_map(static function (CustomerUserScontoBridgeStatusEnum $enum) {
            return $enum->getValue();
        }, $statuses);

        return $this->getCustomerUserRepository()->findBy([
            'scontoBridgeStatus' => $statusStrings,
        ]);
    }
}
