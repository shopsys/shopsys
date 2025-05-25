<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\AiModel;

class AiModelApiToDatabaseDataMapper
{
    /**
     * @param array $apiData
     * @param \Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModel[] $currentModels
     * @param array $apiAiModelData
     * @return \Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModel[]
     */
    public function mapApiDataToDatabaseData(array $apiData, array $currentModels): array
    {
        $aiModels = [];
        $apiAiModels = $apiData['data'] ?? [];

        foreach ($apiAiModels as $apiAiModel) {
            $alreadyExists = false;
            $aiModeData = new AiModelData();
            $aiModeData->name = $apiAiModel['id'] ?? '';

            $aiModel = new AiModel($aiModeData);

            foreach ($currentModels as $currentModel) {
                if ($currentModel->getId() === $aiModel->getName()) {
                    $alreadyExists = true;

                    break;
                }
            }

            if (!$alreadyExists) {
                $aiModels[] = $aiModel;
            }
        }

        return $aiModels;
    }
}
