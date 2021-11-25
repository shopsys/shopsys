<?php

declare(strict_types=1);

namespace App\Model\Product;

class ProductFilesData
{
    /**
     * @var string[]|null[]
     */
    public $assemblyInstructionCode;

    /**
     * @var string[]|null[]
     */
    public $productTypePlanCode;

    public function __construct()
    {
        $this->assemblyInstructionCode = [];
        $this->productTypePlanCode = [];
    }
}
