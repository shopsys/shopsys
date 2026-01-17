<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class StockSettingsDataFactory
{
    public function __construct(
        protected readonly Setting $setting,
        protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
    ) {
    }

    public function getForDomainId(int $domainId): StockSettingsData
    {
        $settings = new StockSettingsData();
        $settings->transfer = $this->setting->getForDomain(Setting::TRANSFER_DAYS_BETWEEN_STOCKS, $domainId);
        $settings->pluginData = $this->pluginCrudExtensionFacade->getAllData('stockSettings', StockSettingsDataFacade::PLUGIN_COMMON_ID);
        $settings->feedDeliveryDaysForOutOfStockProducts = $this->setting->getForDomain(Setting::FEED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS, $domainId);

        return $settings;
    }
}
