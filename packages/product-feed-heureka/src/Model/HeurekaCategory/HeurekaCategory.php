<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Category\Category;

/**
 * @ORM\Table(
 *     name="heureka_category"
 * )
 * @ORM\Entity
 */
class HeurekaCategory
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $fullName;

    /**
     * @ORM\ManyToMany(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category")
     * @ORM\JoinTable(
     *     name="heureka_category_categories",
     *     joinColumns={@ORM\JoinColumn(name="heureka_category_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", unique=true)}
     * )
     */
    protected $categories;

    public function __construct(HeurekaCategoryData $heurekaCategoryData)
    {
        $this->id = $heurekaCategoryData->id;
        $this->name = $heurekaCategoryData->name;
        $this->fullName = $heurekaCategoryData->fullName;
        $this->categories = new ArrayCollection($heurekaCategoryData->categories);
    }

    public function edit(HeurekaCategoryData $heurekaCategoryData)
    {
        $this->name = $heurekaCategoryData->name;
        $this->fullName = $heurekaCategoryData->fullName;
        $this->editCategories($heurekaCategoryData->categories);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    protected function editCategories(array $categories)
    {
        $this->categories->clear();
        foreach ($categories as $category) {
            $this->categories->add($category);
        }
    }

    public function addCategory(Category $category)
    {
        $this->categories->add($category);
    }

    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
