<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Category\Category;

#[ORM\Table(name: 'heureka_category')]
#[ORM\Entity]
class HeurekaCategory
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    protected $id;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $name;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $fullName;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Category\Category>
     */
    #[ORM\JoinTable(name: 'heureka_category_categories')]
    #[ORM\JoinColumn(name: 'heureka_category_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'category_id', referencedColumnName: 'id', unique: true)]
    #[ORM\ManyToMany(targetEntity: Category::class)]
    protected $categories;

    public function __construct(HeurekaCategoryData $heurekaCategoryData)
    {
        $this->id = $heurekaCategoryData->id;
        $this->categories = new ArrayCollection($heurekaCategoryData->categories);
        $this->setData($heurekaCategoryData);
    }

    public function edit(HeurekaCategoryData $heurekaCategoryData): void
    {
        $this->editCategories($heurekaCategoryData->categories);
        $this->setData($heurekaCategoryData);
    }

    protected function setData(HeurekaCategoryData $heurekaCategoryData): void
    {
        $this->name = $heurekaCategoryData->name;
        $this->fullName = $heurekaCategoryData->fullName;
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
