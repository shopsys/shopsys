<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\BlogArticle;

use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;

class BlogArticlePaginatorArgumentsBuilder implements MappingInterface
{
    /**
     * @param array $config
     * @return array
     */
    public function toMappingDefinition(array $config): array
    {
        return [
            'after' => [
                'type' => 'String',
            ],
            'first' => [
                'type' => 'Int',
            ],
            'before' => [
                'type' => 'String',
            ],
            'last' => [
                'type' => 'Int',
            ],
            'onlyHomepageArticles' => [
                'type' => 'Boolean',
                'defaultValue' => false,
            ],
        ];
    }
}
