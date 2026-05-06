<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'zbozi_category')]
#[ORM\UniqueConstraint(name: 'uq_zbozi_category_zbozi_id_locale', columns: ['locale', 'zbozi_id'])]
#[ORM\Entity]
class ZboziCategory
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $zboziId;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $name;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    protected $fullName;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Category\Category>
     */
    #[AsMcpColumn]
    #[ORM\JoinTable(name: 'zbozi_category_categories')]
    #[ORM\JoinColumn(name: 'zbozi_category_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'category_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Category::class)]
    protected $categories;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string')]
    protected $locale;

    public function __construct(ZboziCategoryData $zboziCategoryData)
    {
        $this->zboziId = $zboziCategoryData->zboziId;
        $this->categories = new ArrayCollection($zboziCategoryData->categories);
        $this->setData($zboziCategoryData);
    }

    public function edit(ZboziCategoryData $zboziCategoryData): void
    {
        $this->editCategories($zboziCategoryData->categories);
        $this->setData($zboziCategoryData);
    }

    protected function setData(ZboziCategoryData $zboziCategoryData): void
    {
        $this->name = $zboziCategoryData->name;
        $this->fullName = $zboziCategoryData->fullName;
        $this->locale = $zboziCategoryData->locale;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    protected function editCategories(array $categories): void
    {
        $this->categories->clear();

        foreach ($categories as $category) {
            $this->categories->add($category);
        }
    }

    public function addCategory(Category $category): void
    {
        $this->categories->add($category);
    }

    public function removeCategory(Category $category): void
    {
        $this->categories->removeElement($category);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getZboziId()
    {
        return $this->zboziId;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategories()
    {
        return $this->categories->getValues();
    }
}
