<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class CustomerUserRefreshTokenChainRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCustomerUserRefreshTokenChainRepository(): ObjectRepository
    {
        return $this->em->getRepository(CustomerUserRefreshTokenChain::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain[]
     */
    public function findCustomersTokenChains(CustomerUser $customerUser): array
    {
        return $this->getCustomerUserRefreshTokenChainRepository()->findBy(['customerUser' => $customerUser]);
    }

    /**
     * @param string $deviceId
     */
    public function removeCustomerUserRefreshTokenChainsByDeviceId(string $deviceId): void
    {
        $this->em->createQueryBuilder()
            ->delete(CustomerUserRefreshTokenChain::class, 'curtc')
            ->where('curtc.deviceId = :deviceId')
            ->setParameter('deviceId', $deviceId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function removeAllCustomerUserRefreshTokenChains(CustomerUser $customerUser): void
    {
        if ($customerUser->getId() !== null) {
            $this->em->createQueryBuilder()
                ->delete(CustomerUserRefreshTokenChain::class, 'curtc')
                ->where('curtc.customerUser = :customerUser')
                ->setParameter('customerUser', $customerUser)
                ->getQuery()
                ->execute();
        }

        $customerUser->updateLastSecurityChange();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain $refreshTokenChain
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
