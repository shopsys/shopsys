<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\SeoPage\SeoPageDomain;
use Shopsys\FrameworkBundle\Model\MultidomainEntityClassProvider as BaseMultidomainEntityClassProviderAlias;

class MultidomainEntityClassProvider extends BaseMultidomainEntityClassProviderAlias
{
    /**
     * @return string[]
     */
    public function getManualMultidomainEntitiesNames(): array
    {
        return array_merge(
            parent::getManualMultidomainEntitiesNames(),
            [
                SeoPageDomain::class,
            ],
        );
    }
}
