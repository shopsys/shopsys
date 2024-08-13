<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class StockSettingsDataFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockSettingsData $stockSettingsData
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    public function edit(StockSettingsData $stockSettingsData, DomainConfig $domainConfig): void
    {
        $this->setting->setForDomain(
            Setting::TRANSFER_DAYS_BETWEEN_STOCKS,
            (int)$stockSettingsData->transfer,
            $domainConfig->getId(),
        );

        $this->productRecalculationDispatcher->dispatchAllProducts([ProductExportScopeConfig::SCOPE_STOCKS]);
    }
}
