<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain as BaseProductDomain;

/**
 * @ORM\Table(
 *     name="product_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="product_domain", columns={"product_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 * @property \App\Model\Product\Product $product
 * @method \App\Model\Product\Flag\Flag[] getFlags()
 * @method void setFlags(\App\Model\Product\Flag\Flag[] $flags)
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Product\Flag\Flag> $flags
 * @method __construct(\App\Model\Product\Product $product, int $domainId)
 */
class ProductDomain extends BaseProductDomain
{
}
