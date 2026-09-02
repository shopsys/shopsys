<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Arguments;

use Override;

class ProductReviewPaginatorArgumentsBuilder extends AbstractPaginatorArgumentsBuilder
{
    #[Override]
    public function toMappingDefinition(array $config): array
    {
        $mappingDefinition = parent::toMappingDefinition($config);

        return array_merge($mappingDefinition, [
            'productUuid' => [
                'type' => 'Uuid!',
            ],
            'orderingMode' => [
                'type' => 'ProductReviewOrderingModeEnum',
                'defaultValue' => null,
            ],
        ]);
    }
}
