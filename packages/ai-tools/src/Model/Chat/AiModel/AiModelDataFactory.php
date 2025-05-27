<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\AiModel;

class AiModelDataFactory
{
    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelData
     */
    protected function createInstance(): AiModelData
    {
        return new AiModelData();
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel $aiModel
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelData
     */
    public function createFromAiModel(AiModel $aiModel): AiModelData
    {
        $aiModelData = $this->createInstance();
        $aiModelData->id = $aiModel->getId();
        $aiModelData->name = $aiModel->getName();
        $aiModelData->description = $aiModel->getDescription();
        $aiModelData->isActive = $aiModel->isActive();
        $aiModelData->isDeprecated = $aiModel->isDeprecated();

        return $aiModelData;
    }

    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelData
     */
    public function create(): AiModelData
    {
        return $this->createInstance();
    }
}
