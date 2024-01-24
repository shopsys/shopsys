<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Arguments;

use Shopsys\FrontendApiBundle\Component\Arguments\Exception\MandatoryArgumentMissingException;

class AbstractProductPaginatorArgumentsBuilder extends AbstractPaginatorArgumentsBuilder
{
    protected const CONFIG_ORDER_TYPE_KEY = 'orderingModeType';

    /**
     * @param array $config
     * @return array
     */
    public function toMappingDefinition(array $config): array
    {
        $this->checkMandatoryFields($config);

        $mappingDefinition = parent::toMappingDefinition($config);

        return array_merge($mappingDefinition, [
            'orderingMode' => [
                'type' => $config[static::CONFIG_ORDER_TYPE_KEY],
            ],
            'filter' => [
                'type' => 'ProductFilter',
                'validation' => 'cascade',
            ],
        ]);
    }

    /**
     * @param array $config
     * @throws \Shopsys\FrontendApiBundle\Component\Arguments\Exception\MandatoryArgumentMissingException
     */
    protected function checkMandatoryFields(array $config): void
    {
        if (array_key_exists(static::CONFIG_ORDER_TYPE_KEY, $config) === false) {
            $message = sprintf(
                'Using the `%s`, the key `%s` defining the GraphQL type of the node is required.',
                self::class,
                static::CONFIG_ORDER_TYPE_KEY,
            );

            throw new MandatoryArgumentMissingException($message);
        }
    }
}
