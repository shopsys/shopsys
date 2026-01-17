<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Arguments;

use Override;

class ProductPaginatorArgumentsBuilder extends AbstractProductPaginatorArgumentsBuilder
{
    #[Override]
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
