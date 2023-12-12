<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

/**
 * @ORM\Table(name="category_parameters", indexes={@ORM\Index(name="ordering_idx", columns={"position"})})
 * @ORM\Entity
 */
class CategoryParameter
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Shopsys\FrameworkBundle\Model\Category\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $category;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter")
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id", onDelete="CASCADE", nullable=false )
     */
    protected $parameter;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $collapsed;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param bool $collapsed
     * @param int $position
     */
    public function __construct(Category $category, Parameter $parameter, bool $collapsed, int $position)
    {
        $this->category = $category;
        $this->parameter = $parameter;
        $this->collapsed = $collapsed;
        $this->position = $position;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @return bool
     */
    public function isCollapsed()
    {
        return $this->collapsed;
    }

    /**
     * @param bool $collapsed
     */
    public function setCollapsed(bool $collapsed): void
    {
        $this->collapsed = $collapsed;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
