<?php

declare(strict_types=1);

namespace App\Component\Setting;

use Shopsys\FrameworkBundle\Component\Setting\Setting as BaseSetting;

class Setting extends BaseSetting
{
    public const AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME = 'akeneoTransferProductsLastUpdatedDatetime';
    public const CSP_HEADER = 'cspHeader';
}
