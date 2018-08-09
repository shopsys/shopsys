<?php

namespace Tests\ShopBundle\Database\EntityExtension\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CategoryManyToManyBidirectionalEntity
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Tests\ShopBundle\Database\EntityExtension\Model\ExtendedCategory[]
     *
     * @ORM\ManyToMany(targetEntity="ExtendedCategory", mappedBy="manyToManyBidirectionalEntities")
     */
    protected $categories;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->categories = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Tests\ShopBundle\Database\EntityExtension\Model\ExtendedCategory[]
     */
    public function getCategories(): array
    {
        return $this->categories->getValues();
    }

    public function addCategory(ExtendedCategory $category): void
    {
        $this->categories->add($category);
    }
}
