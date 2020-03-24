<?php

declare(strict_types=1);

namespace App\Model\Category\CategoryProductSeries;

use App\Model\Category\Category;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="category_product_series")
 * @ORM\Entity()
 */
class CategoryProductSeries
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var \App\Model\Category\Category
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Category\Category")
     * @ORM\JoinColumn(nullable=false, name="category_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    private $category;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private $productSeries;

    /**
     * @param \App\Model\Category\Category $category
     * @param int $productSeries
     * @param int $position
     */
    public function __construct(Category $category, int $productSeries, int $position)
    {
        $this->category = $category;
        $this->productSeries = $productSeries;
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return \App\Model\Category\Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return int
     */
    public function getProductSeries(): int
    {
        return $this->productSeries;
    }
}
