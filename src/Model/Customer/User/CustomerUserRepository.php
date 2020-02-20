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
}
