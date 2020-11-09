<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Arguments;

use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;
use Shopsys\FrontendApiBundle\Component\Arguments\Exception\MandatoryArgumentMissingException;

class PaginatorArgumentsBuilder implements MappingInterface
{
    protected const CONFIG_ORDER_TYPE_KEY = 'orderingModeType';

    /**
     * @param array $config
     * @return array
     */
    public function toMappingDefinition(array $config): array
    {
        $this->checkMandatoryFields($config);

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

    /**
     * @param array $config
     * @throws \Shopsys\FrontendApiBundle\Component\Arguments\Exception\MandatoryArgumentMissingException
     */
    protected function checkMandatoryFields(array $config): void
    {
        if (array_key_exists(self::CONFIG_ORDER_TYPE_KEY, $config) === false) {
            $message = sprintf(
                'Using the `%s`, the key `%s` defining the GraphQL type of the node is required.',
                self::class,
                self::CONFIG_ORDER_TYPE_KEY
            );
            throw new MandatoryArgumentMissingException($message);
        }
    }
}
