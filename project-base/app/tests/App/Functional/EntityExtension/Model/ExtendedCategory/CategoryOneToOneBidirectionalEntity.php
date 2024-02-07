<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\ExtendedCategory;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CategoryOneToOneBidirectionalEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\OneToOne(targetEntity="ExtendedCategory", inversedBy="oneToOneBidirectionalEntity")
     * @ORM\JoinColumn(nullable=false, name="category_id", referencedColumnName="id")
     */
    protected ExtendedCategory $category;

    /**
     * @ORM\Column(type="string")
     */
    protected string $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory
     */
    public function getCategory(): ExtendedCategory
    {
        return $this->category;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory $category
     */
    public function setCategory(ExtendedCategory $category): void
    {
        $this->category = $category;
    }
}
