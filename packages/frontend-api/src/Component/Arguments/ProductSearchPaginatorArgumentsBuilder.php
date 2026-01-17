<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Arguments;

use Override;

class ProductSearchPaginatorArgumentsBuilder extends AbstractProductPaginatorArgumentsBuilder
{
    #[Override]
    public function toMappingDefinition(array $config): array
    {
        $this->checkMandatoryFields($config);

        $mappingDefinition = parent::toMappingDefinition($config);

        return array_merge($mappingDefinition, [
            'search' => [
                'type' => 'String',
            ],
        ]);
    }
}
