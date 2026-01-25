<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;

#[ORM\Table(name: 'category_translations')]
#[ORM\Entity]
class CategoryTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     */
    #[Prezent\Translatable(targetEntity: Category::class)]
    protected $translatable;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $name;

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName($name): void
    {
        $this->name = TransformStringHelper::getTrimmedStringOrNullOnEmpty($name);
    }
}
