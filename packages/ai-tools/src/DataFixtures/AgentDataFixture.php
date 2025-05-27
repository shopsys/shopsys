<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\AiToolsBundle\Model\Chat\Agent\AgentDataFactory;
use Shopsys\AiToolsBundle\Model\Chat\Agent\AgentFacade;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class AgentDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const AGENT_ASTROLOG_KEY = 'astrolog';

    public const AGENT_ARTICLE_GENERATOR_KEY = 'articleGenerator';

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\AgentDataFactory $agentDataFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\AgentFacade $agentFacade
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
        $agentData->name = 'Astrolog ' . AiModelDataFixture::GPT_3_5_TURBO;
        $agentData->internalKey = self::AGENT_ASTROLOG_KEY;
        $agentData->enabled = true;
        $agentData->aiModel = $this->getReference(AiModelDataFixture::GPT_3_5_TURBO, AiModel::class);
        $agentData->setup = 'Jsi asistent pro výklad snů jako bys byl astrolog.';

        $agent = $this->agentFacade->create($agentData);
        $this->addReference(self::AGENT_ASTROLOG_KEY, $agent);

        // This agent bellow is added through migration
        $agent = $this->agentFacade->findAgentByInternalKey(self::AGENT_ARTICLE_GENERATOR_KEY);
        $this->addReference(self::AGENT_ARTICLE_GENERATOR_KEY, $agent);
    }

    public function getDependencies()
    {
        return [
            AiModelDataFixture::class,
        ];
    }
}
