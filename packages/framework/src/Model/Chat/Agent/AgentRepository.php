<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\Agent;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class AgentRepository
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
        return $this->em->getRepository(Agent::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent|null
     */
    public function findById(int $id): ?Agent
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent
     */
    public function getDefaultAgent(): Agent
    {
        return $this->getRepository()
            ->createQueryBuilder('a')
            ->where('a.enabled = true')
            ->orderBy('a.id')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllAgentsQueryBuilder(): QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('a');
    }

    /**
     * @param string $internalKey
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent|null
     */
    public function findAgentByInternalKey($internalKey): ?Agent
    {
        return $this->getRepository()->findOneBy([
            'internalKey' => $internalKey,
            'enabled' => true,
        ]);
    }
}
