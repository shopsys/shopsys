<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Tests\App\Functional\EntityExtension\Model\Category\Category;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="product_category_domains",
 *     indexes={@ORM\Index(columns={"category_id", "domain_id"})}
 * )
 */
class ProductCategoryDomain
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Tests\App\Functional\EntityExtension\Model\Product\Product", inversedBy="productCategoryDomains")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected Product $product;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Tests\App\Functional\EntityExtension\Model\Category\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected Category $category;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected int $domainId;
}
