<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\OpenAi\OpenAiModelEnum;
use Shopsys\FrameworkBundle\Model\Chat\Agent\AgentDataFactory;
use Shopsys\FrameworkBundle\Model\Chat\Agent\AgentFacade;

class AgentDataFixture extends AbstractReferenceFixture
{
    public const AGENT_ASTROLOG_KEY = 'astrolog';

    public const AGENT_ARTICLE_GENERATOR_KEY = 'articleGenerator';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentDataFactory $agentDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentFacade $agentFacade
     */
    public function __construct(
        private readonly AgentDataFactory $agentDataFactory,
        private readonly AgentFacade $agentFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $agentData = $this->agentDataFactory->create();
        $agentData->name = 'Astrolog ' . OpenAiModelEnum::GPT_3_5_TURBO;
        $agentData->internalKey = self::AGENT_ASTROLOG_KEY;
        $agentData->enabled = true;
        $agentData->model = OpenAiModelEnum::GPT_3_5_TURBO;
        $agentData->setup = 'Jsi asistent pro výklad snů jako bys byl astrolog.';

        $agent = $this->agentFacade->create($agentData);
        $this->addReference(self::AGENT_ASTROLOG_KEY, $agent);

        $agent = $this->agentFacade->findAgentByInternalKey(self::AGENT_ARTICLE_GENERATOR_KEY);
        $this->addReference(self::AGENT_ARTICLE_GENERATOR_KEY, $agent);
    }
}
