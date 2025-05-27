<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\AiModel;

class AiModelApiToDatabaseDataMapper
{
    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelDataFactory $aiModelDataFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelFactory $aiModelFactory
     */
    public function __construct(
        protected readonly AiModelDataFactory $aiModelDataFactory,
        protected readonly AiModelFactory $aiModelFactory,
    ) {
    }

    /**
     * @param array $apiData
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel[] $currentModels
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel[]
     */
    public function mapApiDataToDatabaseData(array $apiData, array $currentModels): array
    {
        $newAiModels = [];
        $apiAiModels = $apiData['data'] ?? [];
        // 1. připravíme si pole názvů všech aktuálních modelů
        $existingModelNames = array_map(
            static fn (AiModel $model) => $model->getName(),
            $currentModels,
        );

        foreach ($apiAiModels as $apiAiModel) {
            $modelName = $apiAiModel['id'] ?? '';

            // 2. zjednodušená konstrukce
            $alreadyExists = in_array($modelName, $existingModelNames, true);

            if ($alreadyExists) {
                continue;
            }

            $aiModeData = $this->aiModelDataFactory->create();
            $aiModeData->name = $modelName;

            $aiModel = $this->aiModelFactory->create($aiModeData);
            $newAiModels[] = $aiModel;
        }

        return $newAiModels;
    }
}
