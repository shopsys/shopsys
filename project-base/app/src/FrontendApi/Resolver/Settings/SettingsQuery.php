<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Settings;

use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class SettingsQuery extends AbstractQuery
{
    public function __construct()
    {
    }

    /**
     * @return mixed[]
     */
    public function settingsQuery(): array
    {
        /*
         * the fields themselves are resolved with their own resolvers
         * see config/graphql/types/ModelType/Settings/Settings.types.yaml
         */
        return [];
    }
}
