<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="category_translations")
 * @ORM\Entity
 */
class CategoryTranslation extends AbstractTranslation
{
    /**
     * @var \Tests\App\Functional\EntityExtension\Model\Category\Category
     * @Prezent\Translatable(targetEntity="Tests\App\Functional\EntityExtension\Model\Category\Category")
     */
    protected $translatable;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $name = null;
}
