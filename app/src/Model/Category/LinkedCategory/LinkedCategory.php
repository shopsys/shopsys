<?php

declare(strict_types=1);

namespace App\Model\Category\LinkedCategory;

use App\Model\Category\Category;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="linked_categories")
 * @ORM\Entity
 */
class LinkedCategory
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @var \App\Model\Category\Category
     * @ORM\ManyToOne(targetEntity="App\Model\Category\Category")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Category $parentCategory;

    /**
     * @var \App\Model\Category\Category
     * @ORM\ManyToOne(targetEntity="App\Model\Category\Category")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Category $category;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private int $position;

    /**
     * @param \App\Model\Category\Category $parentCategory
     * @param \App\Model\Category\Category $category
     * @param int $position
     */
    public function __construct(Category $parentCategory, Category $category, int $position)
    {
        $this->parentCategory = $parentCategory;
        $this->category = $category;
        $this->position = $position;
    }

    /**
     * @return \App\Model\Category\Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return \App\Model\Category\Category
     */
    public function getParentCategory(): Category
    {
        return $this->parentCategory;
    }
}
