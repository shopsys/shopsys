<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Settings;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class SettingsResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @return array
     */
    public function resolve(): array
    {
        /*
         * the fields themselves are resolved with their own resolvers
         * see config/graphql/types/ModelType/Settings/Settings.types.yaml
         */
        return [];
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'settings'];
    }
}
