<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\SeoPage\SeoPageDomain;
use App\Model\Stock\StockDomain;
use App\Model\Store\StoreDomain;
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
                StockDomain::class,
                StoreDomain::class,
                SeoPageDomain::class,
            ],
        );
    }
}
