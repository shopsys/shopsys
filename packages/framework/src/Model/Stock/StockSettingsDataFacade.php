<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class StockSettingsDataFacade
{
    public const int PLUGIN_COMMON_ID = 0;

    public function __construct(
        protected readonly Setting $setting,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
    ) {
    }

    public function edit(StockSettingsData $stockSettingsData, DomainConfig $domainConfig): void
    {
        $domainId = $domainConfig->getId();

        $this->setting->setForDomain(
            Setting::TRANSFER_DAYS_BETWEEN_STOCKS,
            (int)$stockSettingsData->transfer,
            $domainId,
        );

        $this->pluginCrudExtensionFacade->saveAllData('stockSettings', static::PLUGIN_COMMON_ID, $stockSettingsData->pluginData);

        $this->setting->setForDomain(
            Setting::FEED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS,
            $stockSettingsData->feedDeliveryDaysForOutOfStockProducts,
            $domainId,
        );

        $this->productRecalculationDispatcher->dispatchAllProducts([ProductExportScopeConfig::SCOPE_STOCKS]);
    }
}
