<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\Agent;

class AgentDataFactory
{
    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\Agent\AgentData
     */
    protected function createInstance(): AgentData
    {
        return new AgentData();
    }

    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\Agent\AgentData
     */
    public function create(): AgentData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\Agent $agent
     * @return \Shopsys\AiToolsBundle\Model\Chat\Agent\AgentData
     */
    public function createFromAgent(Agent $agent): AgentData
    {
        $agentData = $this->createInstance();
        $agentData->name = $agent->getName();
        $agentData->enabled = $agent->isEnabled();
        $agentData->aiModel = $agent->getAiModel();
        $agentData->setup = $agent->getSetup();
        $agentData->internalKey = $agent->getInternalKey();
        $agentData->availableAiFunctions = $agent->getAvailableAiFunctions();
        $agentData->vectorStores = $agent->getVectorStores();

        return $agentData;
    }
}
