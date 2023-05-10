<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\ProductTranslation as BaseProductTranslation;

/**
 * @ORM\Table(name="product_translations")
 * @ORM\Entity
 */
class ProductTranslation extends BaseProductTranslation
{
    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $namePrefix;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $nameSufix;

    /**
     * @return string|null
     */
    public function getNamePrefix(): ?string
    {
        return $this->namePrefix;
    }

    /**
     * @param string|null $namePrefix
     */
    public function setNamePrefix(?string $namePrefix): void
    {
        $this->namePrefix = $namePrefix;
    }

    /**
     * @return string|null
     */
    public function getNameSufix(): ?string
    {
        return $this->nameSufix;
    }

    /**
     * @param string|null $nameSufix
     */
    public function setNameSufix(?string $nameSufix): void
    {
        $this->nameSufix = $nameSufix;
    }
}
