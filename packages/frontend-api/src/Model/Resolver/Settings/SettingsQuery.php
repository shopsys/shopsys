<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class SettingsQuery extends AbstractQuery
{
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function settingsQuery(): array
    {
        /*
         * the fields themselves are resolved with their own resolvers
         * see config/graphql-types/Settings/SettingsDecorator.types.yaml
         */
        return [];
    }
}
