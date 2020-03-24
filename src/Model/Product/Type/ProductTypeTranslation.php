<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="product_type_translations")
 * @ORM\Entity
 */
class ProductTypeTranslation extends AbstractTranslation
{
    /**
     * @Prezent\Translatable(targetEntity="App\Model\Product\Type\ProductType")
     */
    protected $translatable;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
