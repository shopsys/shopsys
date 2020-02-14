<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="product_types")
 * @ORM\Entity
 *
 * @method \App\Model\Product\Type\ProductTypeTranslation[] getTranslations()
 * @method \App\Model\Product\Type\ProductTypeTranslation translation(?string $locale)
 */
class ProductType extends AbstractTranslatableEntity
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \App\Model\Product\Type\ProductTypeTranslation[]|\Doctrine\Common\Collections\Collection
     *
     * @Prezent\Translations(targetEntity="App\Model\Product\Type\ProductTypeTranslation")
     */
    protected $translations;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, unique=true)
     */
    protected $akeneoCode;

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    public function __construct(ProductTypeData $productTypeData)
    {
        $this->translations = new ArrayCollection();
        $this->setTranslations($productTypeData);
        $this->akeneoCode = $productTypeData->akeneoCode;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return string
     */
    public function getAkeneoCode(): string
    {
        return $this->akeneoCode;
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    protected function setTranslations(ProductTypeData $productTypeData): void
    {
        foreach ($productTypeData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \App\Model\Product\Type\ProductTypeTranslation
     */
    protected function createTranslation(): ProductTypeTranslation
    {
        return new ProductTypeTranslation();
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    public function edit(ProductTypeData $productTypeData): void
    {
        $this->setTranslations($productTypeData);
        $this->akeneoCode = $productTypeData->akeneoCode;
    }
}
