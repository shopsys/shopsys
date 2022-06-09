<?php

declare(strict_types=1);

namespace App\FrontendApi\Component\Arguments;

use Shopsys\FrontendApiBundle\Component\Arguments\PaginatorArgumentsBuilder as BasePaginatorArgumentsBuilder;

class PaginatorArgumentsBuilder extends BasePaginatorArgumentsBuilder
{
    /**
     * Extended in order to add "cascade" validation to ProductFilter
     *
     * @param array $config
     * @return array
     */
    public function toMappingDefinition(array $config): array
    {
        $mapping = parent::toMappingDefinition($config);
        $mapping['filter'] = [
            'type' => 'ProductFilter',
            'validation' => 'cascade',
        ];

        return $mapping;
    }
}
