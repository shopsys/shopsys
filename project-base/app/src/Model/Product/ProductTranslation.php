<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\ProductTranslation as BaseProductTranslation;
use Shopsys\McpAttributes\Attribute\AsMcpInheritedColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @property \App\Model\Product\Product $translatable
 */
#[AsMcpTable]
#[AsMcpInheritedColumn(fieldName: 'id')]
#[AsMcpInheritedColumn(fieldName: 'locale')]
#[ORM\Table(name: 'product_translations')]
#[ORM\Entity]
class ProductTranslation extends BaseProductTranslation
{
}
