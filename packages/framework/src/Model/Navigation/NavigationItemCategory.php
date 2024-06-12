<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Category\Category;

/**
 * @ORM\Table(name="navigation_item_categories")
 * @ORM\Entity
 */
class NavigationItemCategory implements OrderableEntityInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Navigation\NavigationItem")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $navigationItem;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $columnNumber;

    /**
     * @var int
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $category;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem $navigationItem
     * @param int $columnNumber
     * @param int $position
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
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
    public function getColumnNumber()
    {
        return $this->columnNumber;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem
     */
    public function getNavigationItem()
    {
        return $this->navigationItem;
    }
}
