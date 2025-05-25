<?php

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModel;
use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelApiSourceEnum;
use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelDataFactory;
use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelFacade;

class AiModelDataFixture extends AbstractReferenceFixture
{
    public const GPT_3_5_TURBO = 'gpt-3.5-turbo';

    public function __construct(
        protected readonly AiModelDataFactory $aiModelDataFactory,
        protected readonly AiModelFacade $aiModelFacade,

    )
    {
    }

    public function load(ObjectManager $manager)
    {
        $aiModelData = $this->aiModelDataFactory->create();
        $aiModelData->name = self::GPT_3_5_TURBO;
        $aiModelData->description = 'A powerful language model by OpenAI, suitable for a wide range of tasks.';
        $aiModelData->isActive = true;
        $aiModelData->isDeprecated = false;
        $aiModelData->apiSource = AiModelApiSourceEnum::OPENAI;

        $aiModel = $this->aiModelFacade->create($aiModelData);
        $this->addReference(self::GPT_3_5_TURBO, $aiModel);
    }
}