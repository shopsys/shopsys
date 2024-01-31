<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * Product
 *
 * @ORM\Table(
 *     name="products",
 *     indexes={
 *         @ORM\Index(columns={"variant_type"})
 *     }
 * )
 * @ORM\Entity
 * @method \Tests\App\Functional\EntityExtension\Model\Product\ProductTranslation translation(?string $locale = null)
 */
class Product extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\Product\ProductTranslation>
     * @Prezent\Translations(targetEntity="Tests\App\Functional\EntityExtension\Model\Product\ProductTranslation")
     */
    protected $translations;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected ?string $catnum = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\Product\ProductCategoryDomain>
     * @ORM\OneToMany(
     *   targetEntity="Tests\App\Functional\EntityExtension\Model\Product\ProductCategoryDomain",
     *   mappedBy="product",
     *   orphanRemoval=true,
     *   cascade={"persist"}
     * )
     */
    protected Collection $productCategoryDomains;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\Product\Product>
     * @ORM\OneToMany(targetEntity="Product", mappedBy="mainVariant")
     */
    protected Collection $variants;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="variants")
     * @ORM\JoinColumn(nullable=true, name="main_variant_id", referencedColumnName="id")
     */
    protected ?Product $mainVariant = null;

    /**
     * @ORM\Column(type="string", length=32, nullable=false)
     */
    protected string $variantType;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\Product\ProductDomain>
     * @ORM\OneToMany(targetEntity="Tests\App\Functional\EntityExtension\Model\Product\ProductDomain", mappedBy="product", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected Collection $domains;

    /**
     * @ORM\Column(type="guid", unique=true)
     */
    protected string $uuid;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->productCategoryDomains = new ArrayCollection();
        $this->variants = new ArrayCollection();
        $this->domains = new ArrayCollection();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\Product\ProductTranslation
     */
    protected function createTranslation(): ProductTranslation
    {
        return new ProductTranslation();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setMandatoryData(): void
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->variantType = 'none';
        $this->translation('en')->setName('name');
    }
}
