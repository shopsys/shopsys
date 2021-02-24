<?php

declare(strict_types=1);

namespace App\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

/**
 * @ORM\Table(name="category_parameters", indexes={@ORM\Index(name="ordering_idx", columns={"position"})})
 * @ORM\Entity
 */
class CategoryParameter
{
    /**
     * @var \App\Model\Category\Category
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Model\Category\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $category;

    /**
     * @var \App\Model\Product\Parameter\Parameter
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Model\Product\Parameter\Parameter")
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id", onDelete="CASCADE", nullable=false )
     */
    private $parameter;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $collapsed;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\Product\Parameter\Parameter $parameter
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
     * @return \App\Model\Category\Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return \App\Model\Product\Parameter\Parameter
     */
    public function getParameter(): Parameter
    {
        return $this->parameter;
    }

    /**
     * @return bool
     */
    public function isCollapsed(): bool
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
    public function getPosition(): int
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
