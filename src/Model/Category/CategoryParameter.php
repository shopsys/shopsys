<?php

declare(strict_types=1);

namespace App\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

/**
 * @ORM\Table(name="category_parameters")
 * @ORM\Entity
 */
class CategoryParameter
{
    /**
     * @var \App\Model\Category\Category
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Model\Category\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $category;

    /**
     * @var \App\Model\Product\Parameter\Parameter
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Model\Product\Parameter\Parameter")
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id", onDelete="CASCADE", nullable=false )
     */
    private $parameter;

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\Product\Parameter\Parameter $parameter
     */
    public function __construct(Category $category, Parameter $parameter)
    {
        $this->category = $category;
        $this->parameter = $parameter;
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
}
