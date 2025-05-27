<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\Agent;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\AiToolsBundle\Model\Chat\Chat;

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
     * @return \Shopsys\AiToolsBundle\Model\Chat\Agent\Agent|null
     */
    public function findById(int $id): ?Agent
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\Agent\Agent[]
     */
    public function getEnabledAgents(): array
    {
        return $this->getRepository()
            ->createQueryBuilder('a')
            ->where('a.enabled = true')
            ->orderBy('a.name')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllAgentsQueryBuilder(): QueryBuilder
    {
        return $this->getRepository()
            ->createQueryBuilder('a')
            ->addSelect('m')
            ->join('a.aiModel', 'm');
    }

    /**
     * @param string $internalKey
     * @return \Shopsys\AiToolsBundle\Model\Chat\Agent\Agent|null
     */
    public function findAgentByInternalKey($internalKey): ?Agent
    {
        return $this->getRepository()->findOneBy([
            'internalKey' => $internalKey,
            'enabled' => true,
        ]);
    }

    /**
     * @param int $id
     * @return \Shopsys\AiToolsBundle\Model\Chat\Agent\Agent[]
     */
    public function getAllExceptId(int $id): array
    {
        return $this->getRepository()
            ->createQueryBuilder('a')
            ->where('a.enabled = true')
            ->andWhere('a.id != (:id)')
            ->setParameter('id', $id)
            ->orderBy('a.name')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\Agent $agent
     * @return bool
     */
    public function isAgentUsed(Agent $agent): bool
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('ch.id')
            ->from(Chat::class, 'ch')
            ->setMaxResults(1)
            ->where('ch.agent = :agent')
            ->setParameter('agent', $agent->getId());

        return $queryBuilder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR) !== null;
    }
}
