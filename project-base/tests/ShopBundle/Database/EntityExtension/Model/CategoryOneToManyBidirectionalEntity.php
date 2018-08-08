<?php

namespace Tests\ShopBundle\Database\EntityExtension\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CategoryOneToManyBidirectionalEntity
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
     * @var \Tests\ShopBundle\Database\EntityExtension\Model\ExtendedCategory
     *
     * @ORM\ManyToOne(targetEntity="ExtendedCategory", inversedBy="oneToManyBidirectionalEntity")
     * @ORM\JoinColumn(nullable=false, name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): ExtendedCategory
    {
        return $this->category;
    }

    public function setCategory(ExtendedCategory $category): void
    {
        $this->category = $category;
    }
}
