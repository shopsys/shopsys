<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\ProductTranslation as BaseProductTranslation;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @property \App\Model\Product\Product $translatable
 */
#[AsMcpTable]
#[AsMcpColumn(fieldName: 'id')]
#[AsMcpColumn(fieldName: 'locale')]
#[ORM\Table(name: 'product_translations')]
#[ORM\Entity]
class ProductTranslation extends BaseProductTranslation
{
}
