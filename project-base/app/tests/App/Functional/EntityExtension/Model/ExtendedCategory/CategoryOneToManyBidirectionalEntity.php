<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\ExtendedCategory;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CategoryOneToManyBidirectionalEntity
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: ExtendedCategory::class, inversedBy: 'oneToManyBidirectionalEntities')]
    #[ORM\JoinColumn(nullable: false, name: 'category_id', referencedColumnName: 'id')]
    protected ExtendedCategory $category;

    #[ORM\Column(type: 'string')]
    protected string $name;

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
