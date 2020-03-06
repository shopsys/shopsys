<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;

/**
 * @ORM\Entity
 * @ORM\Table(name="products")
 */
class ExtendedProduct extends Product
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $stringField;

    /**
     * @var \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity
     *
     * @ORM\ManyToOne(targetEntity="UnidirectionalEntity")
     * @ORM\JoinColumn(nullable=true, name="manyToOneUnidirectionalEntity_id", referencedColumnName="id")
     */
    protected $manyToOneUnidirectionalEntity;

    /**
     * @var \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity
     *
     * @ORM\OneToOne(targetEntity="UnidirectionalEntity")
     * @ORM\JoinColumn(nullable=true, name="oneToOneUnidirectionalEntity_id", referencedColumnName="id")
     */
    protected $oneToOneUnidirectionalEntity;

    /**
     * @var \Tests\App\Functional\EntityExtension\Model\ProductOneToOneBidirectionalEntity
     *
     * @ORM\OneToOne(targetEntity="ProductOneToOneBidirectionalEntity", mappedBy="product")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $oneToOneBidirectionalEntity;

    /**
     * @var \Tests\App\Functional\EntityExtension\Model\ExtendedProduct
     *
     * @ORM\OneToOne(targetEntity="ExtendedProduct")
     * @ORM\JoinColumn(nullable=true, name="oneToOneSelfReferencing_id", referencedColumnName="id")
     */
    protected $oneToOneSelfReferencingEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\ProductOneToManyBidirectionalEntity[]
     *
     * @ORM\OneToMany(targetEntity="ProductOneToManyBidirectionalEntity", mappedBy="product")
     */
    protected $oneToManyBidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity[]
     *
     * @ORM\ManyToMany(targetEntity="UnidirectionalEntity")
     * @ORM\JoinTable(name="products_oneToManyUnidirectionalWithJoinTableEntity",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="oneToManyUnidirectionalWithJoinTableEntity_id", referencedColumnName="id", unique=true)}
     *      )
     */
    protected $oneToManyUnidirectionalWithJoinTableEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\ExtendedProduct[]
     *
     * @ORM\OneToMany(targetEntity="ExtendedProduct", mappedBy="oneToManySelfReferencingInverseEntity")
     */
    protected $oneToManySelfReferencingEntities;

    /**
     * @var \Tests\App\Functional\EntityExtension\Model\ExtendedProduct
     *
     * @ORM\ManyToOne(targetEntity="ExtendedProduct", inversedBy="oneToManySelfReferencingEntities")
     * @ORM\JoinColumn(nullable=true, name="oneToManySelfReferencingParent_id", referencedColumnName="id")
     */
    protected $oneToManySelfReferencingInverseEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity[]
     *
     * @ORM\ManyToMany(targetEntity="UnidirectionalEntity")
     * @ORM\JoinTable(name="products_manyToManyUnidirectionalEntity",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="manyToManyUnidirectionalEntity_id", referencedColumnName="id")}
     *      )
     */
    protected $manyToManyUnidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\ProductManyToManyBidirectionalEntity[]
     *
     * @ORM\ManyToMany(targetEntity="ProductManyToManyBidirectionalEntity", inversedBy="products")
     * @ORM\JoinTable(name="products_manyToManyBidirectionalEntity")
     */
    protected $manyToManyBidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\ExtendedProduct[]
     *
     * @ORM\ManyToMany(targetEntity="ExtendedProduct", mappedBy="manyToManySelfReferencingInverseEntities")
     */
    protected $manyToManySelfReferencingEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\ExtendedProduct[]
     *
     * @ORM\ManyToMany(targetEntity="ExtendedProduct", inversedBy="manyToManySelfReferencingEntities")
     * @ORM\JoinTable(name="products_manyToManySelfReferencing",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="manyToManySelfReferencing_id", referencedColumnName="id")}
     *      )
     */
    protected $manyToManySelfReferencingInverseEntities;

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product[]|null $variants
     */
    protected function __construct(ProductData $productData, ?array $variants = null)
    {
        parent::__construct($productData, $variants);
        $this->oneToManyBidirectionalEntities = new ArrayCollection();
        $this->oneToManyUnidirectionalWithJoinTableEntities = new ArrayCollection();
        $this->oneToManySelfReferencingEntities = new ArrayCollection();
        $this->manyToManyUnidirectionalEntities = new ArrayCollection();
        $this->manyToManyBidirectionalEntities = new ArrayCollection();
        $this->manyToManySelfReferencingEntities = new ArrayCollection();
        $this->manyToManySelfReferencingInverseEntities = new ArrayCollection();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity
     */
    public function getManyToOneUnidirectionalEntity(): UnidirectionalEntity
    {
        return $this->manyToOneUnidirectionalEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity $manyToOneUnidirectionalEntity
     */
    public function setManyToOneUnidirectionalEntity(UnidirectionalEntity $manyToOneUnidirectionalEntity): void
    {
        $this->manyToOneUnidirectionalEntity = $manyToOneUnidirectionalEntity;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity
     */
    public function getOneToOneUnidirectionalEntity(): UnidirectionalEntity
    {
        return $this->oneToOneUnidirectionalEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity $oneToOneUnidirectionalEntity
     */
    public function setOneToOneUnidirectionalEntity(UnidirectionalEntity $oneToOneUnidirectionalEntity): void
    {
        $this->oneToOneUnidirectionalEntity = $oneToOneUnidirectionalEntity;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ProductOneToOneBidirectionalEntity
     */
    public function getOneToOneBidirectionalEntity(): ProductOneToOneBidirectionalEntity
    {
        return $this->oneToOneBidirectionalEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ProductOneToOneBidirectionalEntity $oneToOneBidirectionalEntity
     */
    public function setOneToOneBidirectionalEntity(ProductOneToOneBidirectionalEntity $oneToOneBidirectionalEntity): void
    {
        $oneToOneBidirectionalEntity->setProduct($this);
        $this->oneToOneBidirectionalEntity = $oneToOneBidirectionalEntity;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct
     */
    public function getOneToOneSelfReferencingEntity(): self
    {
        return $this->oneToOneSelfReferencingEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct $oneToOneSelfReferencingEntity
     */
    public function setOneToOneSelfReferencingEntity(self $oneToOneSelfReferencingEntity): void
    {
        $this->oneToOneSelfReferencingEntity = $oneToOneSelfReferencingEntity;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ProductOneToManyBidirectionalEntity[]
     */
    public function getOneToManyBidirectionalEntities(): array
    {
        return $this->oneToManyBidirectionalEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ProductOneToManyBidirectionalEntity $oneToManyBidirectionalEntity
     */
    public function addOneToManyBidirectionalEntity(ProductOneToManyBidirectionalEntity $oneToManyBidirectionalEntity): void
    {
        $oneToManyBidirectionalEntity->setProduct($this);
        $this->oneToManyBidirectionalEntities->add($oneToManyBidirectionalEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity[]
     */
    public function getOneToManyUnidirectionalWithJoinTableEntities(): array
    {
        return $this->oneToManyUnidirectionalWithJoinTableEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity $oneToManyUnidirectionalWithJoinTableEntity
     */
    public function addOneToManyUnidirectionalWithJoinTableEntity(UnidirectionalEntity $oneToManyUnidirectionalWithJoinTableEntity): void
    {
        $this->oneToManyUnidirectionalWithJoinTableEntities->add($oneToManyUnidirectionalWithJoinTableEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct[]
     */
    public function getOneToManySelfReferencingEntities(): array
    {
        return $this->oneToManySelfReferencingEntities->getValues();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct
     */
    public function getOneToManySelfReferencingInverseEntity(): self
    {
        return $this->oneToManySelfReferencingInverseEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct $oneToManySelfReferencing
     */
    public function addOneToManySelfReferencingEntity(self $oneToManySelfReferencing): void
    {
        $oneToManySelfReferencing->oneToManySelfReferencingInverseEntity = $this;
        $this->oneToManySelfReferencingEntities->add($oneToManySelfReferencing);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity[]
     */
    public function getManyToManyUnidirectionalEntities(): array
    {
        return $this->manyToManyUnidirectionalEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity $manyToManyUnidirectionalEntity
     */
    public function addManyToManyUnidirectionalEntity(UnidirectionalEntity $manyToManyUnidirectionalEntity): void
    {
        $this->manyToManyUnidirectionalEntities->add($manyToManyUnidirectionalEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ProductManyToManyBidirectionalEntity[]
     */
    public function getManyToManyBidirectionalEntities(): array
    {
        return $this->manyToManyBidirectionalEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ProductManyToManyBidirectionalEntity $manyToManyBidirectionalEntity
     */
    public function addManyToManyBidirectionalEntity(ProductManyToManyBidirectionalEntity $manyToManyBidirectionalEntity): void
    {
        $manyToManyBidirectionalEntity->addProduct($this);
        $this->manyToManyBidirectionalEntities->add($manyToManyBidirectionalEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct[]
     */
    public function getManyToManySelfReferencingEntities(): array
    {
        return $this->manyToManySelfReferencingEntities->getValues();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct[]
     */
    public function getManyToManySelfReferencingInverseEntities()
    {
        return $this->manyToManySelfReferencingInverseEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct $manyToManySelfReferencing
     */
    public function addManyToManySelfReferencingEntity(self $manyToManySelfReferencing): void
    {
        $manyToManySelfReferencing->manyToManySelfReferencingInverseEntities->add($this);
        $this->manyToManySelfReferencingEntities->add($manyToManySelfReferencing);
    }

    /**
     * @return string|null
     */
    public function getStringField()
    {
        return $this->stringField;
    }

    /**
     * @param string|null $stringField
     */
    public function setStringField($stringField): void
    {
        $this->stringField = $stringField;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProductTranslation
     */
    protected function createTranslation(): ExtendedProductTranslation
    {
        return new ExtendedProductTranslation();
    }
}
