<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Chat\ChatDataFactory;
use Shopsys\FrameworkBundle\Model\Chat\ChatFacade;

class ChatDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatFacade $chatFacade
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatDataFactory $chatDataFactory
     */
    public function __construct(
        private readonly ChatFacade $chatFacade,
        private readonly ChatDataFactory $chatDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager)
    {
        $chatData = $this->chatDataFactory->create();
        $chatData->identifier = '4cdf3169-45d6-4705-9791-3521107a4041';
        $chatData->agent = $this->getReference(AgentDataFixture::AGENT_ASTROLOG_KEY);
        $this->chatFacade->create($chatData);

        $chatData = $this->chatDataFactory->create();
        $chatData->identifier = 'ff7b0683-2a35-4421-9dd9-47716109a685';
        $chatData->agent = $this->getReference(AgentDataFixture::AGENT_ARTICLE_GENERATOR_KEY);
        $this->chatFacade->create($chatData);
    }

    public function getDependencies()
    {
        return [
            AgentDataFixture::class,
        ];
    }
}
