<?php

namespace Shopsys\FrameworkBundle\Model\Product;

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
     * @Prezent\Translatable(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     */
    protected $translatable;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="tsvector", nullable=false)
     */
    protected $nameTsvector;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $variantAlias;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getVariantAlias(): ?string
    {
        return $this->variantAlias;
    }

    /**
     * @param string|null $variantAlias
     */
    public function setVariantAlias($variantAlias)
    {
        $this->variantAlias = $variantAlias;
    }
}
