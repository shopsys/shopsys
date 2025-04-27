<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage;

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
     * @param string $identifier
     * @return \Shopsys\FrameworkBundle\Model\Chat\Chat|null
     */
    public function findByIdentifier(string $identifier): ?Chat
    {
        return $this->getRepository()->findOneBy(['identifier' => $identifier]);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Chat\Chat|null
     */
    public function findById(int $id): ?Chat
    {
        return $this->getRepository()->find($id);
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

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllChatsQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()->select('ch, a, m')
            ->from(Chat::class, 'ch')
            ->join('ch.agent', 'a')
            ->leftJoin('ch.messages', 'm', Join::WITH, 'm.id = (SELECT MIN(m2.id) FROM ' . ChatMessage::class . ' m2 WHERE m2.chat = ch)')
        ;
    }
}
