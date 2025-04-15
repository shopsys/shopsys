<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\Agent;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AgentFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentFactory $agentFactory
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentRepository $agentRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AgentFactory $agentFactory,
        protected readonly AgentRepository $agentRepository,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent
     */
    public function getDefaultAgent(): Agent
    {
        return $this->agentRepository->getDefaultAgent();
    }

    /**
     * @param string $internalKey
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent|null
     */
    public function findAgentByInternalKey($internalKey): ?Agent
    {
        return $this->agentRepository->findAgentByInternalKey($internalKey);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentData $agentData
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent
     */
    public function create(AgentData $agentData): Agent
    {
        $agent = $this->agentFactory->create($agentData);
        $this->em->persist($agent);
        $this->em->flush();

        return $agent;
    }

    /**
     * @param int $id
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentData $agentData
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent
     */
    public function edit(int $id, AgentData $agentData): Agent
    {
        $agent = $this->getById($id);
        $agent->edit($agentData);
        $this->em->flush();

        return $agent;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent
     */
    public function getById(int $id): Agent
    {
        $agent = $this->agentRepository->findById($id);

        if ($agent === null) {
            throw new NotFoundHttpException(sprintf('Agent with id "%s" does not exist.', $id));
        }

        return $agent;
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $agent = $this->getById($id);
        $this->em->remove($agent);
        $this->em->flush();
    }
}
