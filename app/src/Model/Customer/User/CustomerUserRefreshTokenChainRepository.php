<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use DateTime;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainRepository as BaseCustomerUserRefreshTokenChainRepository;

/**
 * @method \App\Model\Customer\User\CustomerUserRefreshTokenChain[] findCustomersTokenChains(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method removeAllCustomerUserRefreshTokenChains(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class CustomerUserRefreshTokenChainRepository extends BaseCustomerUserRefreshTokenChainRepository
{
    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $deviceId
     * @return array
     */
    public function findCustomersTokenChainsByDeviceId(CustomerUser $customerUser, string $deviceId): array
    {
        return $this->getCustomerUserRefreshTokenChainRepository()->createQueryBuilder('curtc')
            ->where('curtc.customerUser = :customerUser')
            ->andWhere('curtc.expiredAt >= :now')
            ->andWhere('curtc.deviceId = :deviceId')
            ->setParameters([
                'customerUser' => $customerUser,
                'now' => new DateTime(),
                'deviceId' => $deviceId,
            ])
            ->getQuery()->getResult();
    }

    /**
     * @param \App\Model\Customer\User\CustomerUserRefreshTokenChain $refreshTokenChain
     */
    public function removeCustomerRefreshTokenChain(CustomerUserRefreshTokenChain $refreshTokenChain): void
    {
        $this->em->remove($refreshTokenChain);
        $this->em->flush();
    }

    public function removeOldCustomerRefreshTokenChains(): void
    {
        $this->em->createQueryBuilder()
            ->delete(CustomerUserRefreshTokenChain::class, 'curtc')
            ->where('curtc.expiredAt < :now')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->execute();
    }
}
