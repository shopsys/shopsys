<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Category\Category;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="promo_code_categories")
 * @ORM\Entity
 */
class PromoCodeCategory
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCode
     * @ORM\ManyToOne(targetEntity="App\Model\Order\PromoCode\PromoCode")
     * @ORM\JoinColumn(nullable=false, name="promo_code_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $promoCode;

    /**
     * @var \App\Model\Category\Category
     * @ORM\ManyToOne(targetEntity="App\Model\Category\Category")
     * @ORM\JoinColumn(nullable=false, name="category_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $category;

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Category\Category $category
     */
    public function __construct(PromoCode $promoCode, Category $category)
    {
        $this->promoCode = $promoCode;
        $this->category = $category;
    }

    /**
     * @return \App\Model\Category\Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }
}
