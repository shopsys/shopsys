<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\ExtendedCategory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CategoryManyToManyBidirectionalEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory>
     * @ORM\ManyToMany(targetEntity="ExtendedCategory", mappedBy="manyToManyBidirectionalEntities")
     */
    protected Collection $categories;

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
        $this->categories = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory[]
     */
    public function getCategories(): array
    {
        return $this->categories->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory $category
     */
    public function addCategory(ExtendedCategory $category): void
    {
        $this->categories->add($category);
    }
}
