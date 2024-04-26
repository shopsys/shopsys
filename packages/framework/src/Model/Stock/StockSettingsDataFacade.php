<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class StockSettingsDataFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockSettingsData $stockSettingsData
     */
    public function edit(StockSettingsData $stockSettingsData): void
    {
        $this->setting->setForDomain(
            Setting::TRANSFER_DAYS_BETWEEN_STOCKS,
            (int)$stockSettingsData->transfer,
            $this->adminDomainTabsFacade->getSelectedDomainId(),
        );

        $this->productRecalculationDispatcher->dispatchAllProducts([ProductExportScopeConfig::SCOPE_STOCKS]);
    }
}
