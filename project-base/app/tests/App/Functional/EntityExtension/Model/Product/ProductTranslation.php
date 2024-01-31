<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="product_translations")
 * @ORM\Entity
 */
class ProductTranslation extends AbstractTranslation
{
    /**
     * @var \Tests\App\Functional\EntityExtension\Model\Product\Product
     * @Prezent\Translatable(targetEntity="Tests\App\Functional\EntityExtension\Model\Product\Product")
     */
    protected $translatable;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $name = null;

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
