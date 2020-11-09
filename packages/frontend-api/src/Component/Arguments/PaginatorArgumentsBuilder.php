<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Arguments;

use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;

class PaginatorArgumentsBuilder implements MappingInterface
{
    protected const CONFIG_ORDER_TYPE_KEY = 'orderingModeType';

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
            'orderingMode' => [
                'type' => $config[self::CONFIG_ORDER_TYPE_KEY],
            ],
        ];
    }
}
