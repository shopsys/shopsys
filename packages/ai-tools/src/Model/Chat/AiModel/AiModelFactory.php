<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\AiModel;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AiModelFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelData $aiModelData
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel
     */
    public function create(AiModelData $aiModelData): AiModel
    {
        $entityClassName = $this->entityNameResolver->resolve(AiModel::class);

        return new $entityClassName($aiModelData);
    }
}
