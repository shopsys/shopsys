<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\ExtendedProduct;

use Doctrine\ORM\Mapping as ORM;
use Tests\App\Functional\EntityExtension\Model\Product\ProductTranslation;

#[ORM\Table(name: 'product_translations')]
#[ORM\Entity]
class ExtendedProductTranslation extends ProductTranslation
{
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected string $productDetailName;
}
