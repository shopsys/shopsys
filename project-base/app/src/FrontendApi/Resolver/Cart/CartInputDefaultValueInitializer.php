<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Cart;

use Overblog\GraphQLBundle\Definition\Argument;

class CartInputDefaultValueInitializer
{
    /**
     * Default values are not properly propagated from configuration
     * This should be fixed after update to overblog/graphql-bundle 0.14
     *
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array
     */
    public static function initializeDefaultValues(Argument $argument): array
    {
        return $argument['cartInput'] ?? ['cartUuid' => null];
    }
}
