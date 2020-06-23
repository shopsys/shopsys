<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class CustomerUserRefreshTokenChainRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

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
    }
}
