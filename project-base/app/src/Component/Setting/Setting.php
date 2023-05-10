<?php

declare(strict_types=1);

namespace App\Component\Setting;

use Shopsys\FrameworkBundle\Component\Setting\Setting as BaseSetting;

class Setting extends BaseSetting
{
    public const AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME = 'akeneoTransferProductsLastUpdatedDatetime';
    public const DELIVERY_DAYS_ON_STOCK = 'deliveryDayOnStock';
    public const TRANSFER_DAYS_BETWEEN_STOCKS = 'transferDaysBetweenStocks';
    public const CSP_HEADER = 'cspHeader';
}
