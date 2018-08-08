<?php

namespace Shopsys\FrameworkBundle\Model\Category\TopCategory;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Category\Category;

/**
 * @ORM\Table(name="categories_top")
 * @ORM\Entity
 */
class TopCategory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category")
     * @ORM\JoinColumn(nullable=false, name="category_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $category;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $domainId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @param int $domainId
     * @param int $position
     */
    public function __construct(Category $category, $domainId, $position)
    {
        $this->category = $category;
        $this->domainId = $domainId;
        $this->position = $position;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
