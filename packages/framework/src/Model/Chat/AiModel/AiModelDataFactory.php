<?php

namespace Shopsys\FrameworkBundle\Model\Chat\AiModel;

class AiModelDataFactory
{
    /**
     * @param AiModel $aiModel
     * @return AiModelData
     */
    public function createFromAiModel(AiModel $aiModel): AiModelData {
        $aiModelData = new AiModelData();
        $aiModelData->id = $aiModel->getId();
        $aiModelData->name = $aiModel->getName();
        $aiModelData->description = $aiModel->getDescription();
        $aiModelData->isActive = $aiModel->isActive();
        $aiModelData->isDeprecated = $aiModel->isDeprecated();

        return $aiModelData;
    }

    public function create()
    {
        $aiModelData = new AiModelData();
        $aiModelData->id = '';
        $aiModelData->name = '';
        $aiModelData->description = '';
        $aiModelData->isActive = false;
        $aiModelData->isDeprecated = false;

        return $aiModelData;
    }
}