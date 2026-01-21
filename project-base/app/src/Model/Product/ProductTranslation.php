<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\ProductTranslation as BaseProductTranslation;

/**
 * @property \App\Model\Product\Product $translatable
 */
#[ORM\Table(name: 'product_translations')]
#[ORM\Entity]
class ProductTranslation extends BaseProductTranslation
{
}
