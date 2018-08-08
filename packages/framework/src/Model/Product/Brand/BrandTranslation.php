<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="brand_translations")
 * @ORM\Entity
 */
class BrandTranslation extends AbstractTranslation
{
    /**
     * @Prezent\Translatable(targetEntity="Shopsys\FrameworkBundle\Model\Product\Brand\Brand")
     */
    protected $translatable;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
