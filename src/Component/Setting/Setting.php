<?php

declare(strict_types=1);

namespace App\Component\Setting;

use Shopsys\FrameworkBundle\Component\Setting\Setting as BaseSetting;

class Setting extends BaseSetting
{
    public const AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME = 'akeneoTransferProductsLastUpdatedDatetime';
    public const AKENEO_TRANSFER_FLAGS_LAST_UPDATED_DATETIME = 'akeneoTransferFlagsLastUpdatedDatetime';
    public const DELIVERY_DAYS_ON_STOCK = 'deliveryDayOnStock';
    public const TRANSFER_DAYS_BETWEEN_STOCKS = 'transferDaysBetweenStocks';
    public const SCONTO_BRIDGE_TRANSFER_CUSTOMERS_LAST_UPDATED_DATETIME = 'scontoBridgeTransferCustomersLastUpdatedDatetime';
    public const SCONTO_BRIDGE_TRANSFER_PRODUCT_STOCK_LAST_UPDATED_DATETIME = 'scontoBridgeTransferProductStockLastUpdatedDatetime';
    public const SCONTO_BRIDGE_TRANSFER_FUTURE_PRODUCT_STOCK_LAST_UPDATED_DATETIME = 'scontoBridgeTransferFutureProductStockLastUpdatedDatetime';
    public const TARGITO_LAST_SYNC_DATETIME = 'targitoLastSyncDatetime';
}
