<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

use App\Model\Category\Category;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

/**
 * @ORM\Table(name="horizontal_menu_item_categories")
 * @ORM\Entity
 */
class HorizontalMenuItemCategory implements OrderableEntityInterface
{
    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItem
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\HorizontalMenu\HorizontalMenuItem")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $horizontalMenuItem;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $columnNumber;

    /**
     * @var int
     *
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var \App\Model\Category\Category
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Category\Category")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $category;

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @param int $columnNumber
     * @param int $position
     * @param \App\Model\Category\Category $category
     */
    public function __construct(
        HorizontalMenuItem $horizontalMenuItem,
        int $columnNumber,
        int $position,
        Category $category
    ) {
        $this->horizontalMenuItem = $horizontalMenuItem;
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
     * @return \App\Model\HorizontalMenu\HorizontalMenuItem
     */
    public function getHorizontalMenuItem(): HorizontalMenuItem
    {
        return $this->horizontalMenuItem;
    }
}
