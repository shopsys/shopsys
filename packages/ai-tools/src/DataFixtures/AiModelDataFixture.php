<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelDataFactory;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelFacade;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class AiModelDataFixture extends AbstractReferenceFixture
{
    public const GPT_3_5_TURBO = 'gpt-3.5-turbo';

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelDataFactory $aiModelDataFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelFacade $aiModelFacade
     */
    public function __construct(
        protected readonly AiModelDataFactory $aiModelDataFactory,
        protected readonly AiModelFacade $aiModelFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager)
    {
        // This model bellow is added through migration
        $aiModel = $this->aiModelFacade->findAiModelByName(self::GPT_3_5_TURBO);
        $this->addReference(self::GPT_3_5_TURBO, $aiModel);
    }
}
