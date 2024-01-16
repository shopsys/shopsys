<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Arguments;

class ProductPaginatorArgumentsBuilder extends AbstractProductPaginatorArgumentsBuilder
{
    /**
     * @param array $config
     * @return array
     */
    public function toMappingDefinition(array $config): array
    {
        $this->checkMandatoryFields($config);

        $mappingDefinition = parent::toMappingDefinition($config);

        return array_merge($mappingDefinition, [
            'categorySlug' => [
                'type' => 'String',
            ],
            'brandSlug' => [
                'type' => 'String',
            ],
            'flagSlug' => [
                'type' => 'String',
            ],
        ]);
    }
}
