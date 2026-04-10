<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'category_parameters')]
#[ORM\Index(name: 'ordering_idx', columns: ['position'])]
#[ORM\Entity]
class CategoryParameter
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Category::class)]
    protected $category;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'parameter_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Parameter::class)]
    protected $parameter;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $collapsed;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $position;

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
    public function setPosition($position): void
    {
        $this->position = $position;
    }
}
