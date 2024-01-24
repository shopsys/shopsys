<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Arguments;

class BlogArticlePaginatorArgumentsBuilder extends AbstractPaginatorArgumentsBuilder
{
    /**
     * @param array $config
     * @return array
     */
    public function toMappingDefinition(array $config): array
    {
        $mappingDefinition = parent::toMappingDefinition($config);

        return array_merge($mappingDefinition, [
            'onlyHomepageArticles' => [
                'type' => 'Boolean',
                'defaultValue' => false,
            ],
        ]);
    }
}
