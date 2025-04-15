<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\Agent;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AgentFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentData $agentData
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent
     */
    public function create(AgentData $agentData): Agent
    {
        $entityClassName = $this->entityNameResolver->resolve(Agent::class);

        return new $entityClassName($agentData);
    }
}
