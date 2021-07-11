<?php

declare(strict_types=1);

namespace App\Model\Store;

class ProductStoreData
{
    public bool $productExposed;

    public string $name;

    public int $storeId;

    public function __construct()
    {
        $this->productExposed = false;
    }
}
