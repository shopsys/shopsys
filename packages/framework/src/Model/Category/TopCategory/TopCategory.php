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

    public function __construct(Category $category, int $domainId, int $position)
    {
        $this->category = $category;
        $this->domainId = $domainId;
        $this->position = $position;
    }

    public function getCategory(): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->category;
    }
}
