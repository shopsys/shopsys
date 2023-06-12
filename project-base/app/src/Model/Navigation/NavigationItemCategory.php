<?php

declare(strict_types=1);

namespace App\Model\Navigation;

use App\Model\Category\Category;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

/**
 * @ORM\Table(name="navigation_item_categories")
 * @ORM\Entity
 */
class NavigationItemCategory implements OrderableEntityInterface
{
    /**
     * @var \App\Model\Navigation\NavigationItem
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Navigation\NavigationItem")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private NavigationItem $navigationItem;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private int $columnNumber;

    /**
     * @var int
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private int $position;

    /**
     * @var \App\Model\Category\Category
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Category\Category")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Category $category;

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @param int $columnNumber
     * @param int $position
     * @param \App\Model\Category\Category $category
     */
    public function __construct(
        NavigationItem $navigationItem,
        int $columnNumber,
        int $position,
        Category $category,
    ) {
        $this->navigationItem = $navigationItem;
        $this->columnNumber = $columnNumber;
        $this->position = $position;
        $this->category = $category;
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getColumnNumber(): int
    {
        return $this->columnNumber;
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
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return \App\Model\Navigation\NavigationItem
     */
    public function getNavigationItem(): NavigationItem
    {
        return $this->navigationItem;
    }
}
