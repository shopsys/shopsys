<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ChatRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(Chat::class);
    }

    /**
     * @param string $userIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Chat\Chat|null
     */
    public function findByUserIdentifier(string $userIdentifier): ?Chat
    {
        return $this->getRepository()->findOneBy(['userIdentifier' => $userIdentifier]);
    }

    public function removeOldChats(): void
    {
        $this->getRepository()->createQueryBuilder('ch')
            ->delete(Chat::class, 'ch')
            ->where('ch.updatedAt < :monthDate')
            ->setParameter('monthDate', new DateTime('today midnight -30 day'))
            ->getQuery()
            ->execute();
    }
}
