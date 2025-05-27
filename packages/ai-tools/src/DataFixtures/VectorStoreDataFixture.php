<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreDataFactory;
use Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreFacade;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class VectorStoreDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreDataFactory $vectorStoreDataFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreFacade $vectorStoreFacade
     */
    public function __construct(
        private readonly VectorStoreDataFactory $vectorStoreDataFactory,
        private readonly VectorStoreFacade $vectorStoreFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager)
    {
        $vectorStoreData = $this->vectorStoreDataFactory->create();
        $vectorStoreData->externalId = t('just example', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN);
        $vectorStoreData->name = t('Example', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN);
        $vectorStoreData->description = t('just example without representation is API', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN);

        $this->vectorStoreFacade->create($vectorStoreData);
    }
}
