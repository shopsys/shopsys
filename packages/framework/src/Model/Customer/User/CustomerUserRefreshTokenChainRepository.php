<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Psr\Clock\ClockInterface;

class CustomerUserRefreshTokenChainRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCustomerUserRefreshTokenChainRepository(): ObjectRepository
    {
        return $this->em->getRepository(CustomerUserRefreshTokenChain::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain[]
     */
    public function findCustomersTokenChains(CustomerUser $customerUser): array
    {
        return $this->getCustomerUserRefreshTokenChainRepository()->findBy(['customerUser' => $customerUser]);
    }

    public function removeCustomerUserRefreshTokenChainsByDeviceId(string $deviceId): void
    {
        $this->em->createQueryBuilder()
            ->delete(CustomerUserRefreshTokenChain::class, 'curtc')
            ->where('curtc.deviceId = :deviceId')
            ->setParameter('deviceId', $deviceId)
            ->getQuery()
            ->execute();
    }

    public function removeAllCustomerUserRefreshTokenChains(
        CustomerUser $customerUser,
        ?string $excludedDeviceId = null,
    ): void {
        if ($customerUser->getId() !== null) {
            $queryBuilder = $this->em->createQueryBuilder()
                ->delete(CustomerUserRefreshTokenChain::class, 'curtc')
                ->where('curtc.customerUser = :customerUser')
                ->setParameter('customerUser', $customerUser);

            if ($excludedDeviceId !== null) {
                $queryBuilder
                    ->andWhere('curtc.deviceId != :deviceId')
                    ->setParameter('deviceId', $excludedDeviceId);
            }

            $queryBuilder
                ->getQuery()
                ->execute();
        }

        $customerUser->updateLastSecurityChange();
        $this->em->flush();
    }

    public function findCustomersTokenChainsByDeviceId(CustomerUser $customerUser, string $deviceId): array
    {
        return $this->getCustomerUserRefreshTokenChainRepository()->createQueryBuilder('curtc')
            ->where('curtc.customerUser = :customerUser')
            ->andWhere('curtc.expiredAt >= :now')
            ->andWhere('curtc.deviceId = :deviceId')
            ->setParameter('customerUser', $customerUser)
            ->setParameter('now', $this->clock->now())
            ->setParameter('deviceId', $deviceId)
            ->getQuery()->getResult();
    }

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
            ->setParameter('now', $this->clock->now())
            ->getQuery()
            ->execute();
    }
}
