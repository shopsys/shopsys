<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Functional\EntityExtension\Model;

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
     * @var \Tests\FrameworkBundle\Functional\EntityExtension\Model\Product
     * @Prezent\Translatable(targetEntity="Tests\FrameworkBundle\Functional\EntityExtension\Model\Product")
     */
    protected $translatable;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $name;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
