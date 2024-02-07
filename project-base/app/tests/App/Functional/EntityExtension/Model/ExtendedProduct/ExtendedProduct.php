<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\ExtendedProduct;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Tests\App\Functional\EntityExtension\Model\Product\Product;
use Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="products")
 */
class ExtendedProduct extends Product
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $stringField;

    /**
     * @ORM\ManyToOne(targetEntity="Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity")
     * @ORM\JoinColumn(nullable=true, name="manyToOneUnidirectionalEntity_id", referencedColumnName="id")
     */
    protected UnidirectionalEntity $manyToOneUnidirectionalEntity;

    /**
     * @ORM\OneToOne(targetEntity="Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity")
     * @ORM\JoinColumn(nullable=true, name="oneToOneUnidirectionalEntity_id", referencedColumnName="id")
     */
    protected UnidirectionalEntity $oneToOneUnidirectionalEntity;

    /**
     * @ORM\OneToOne(targetEntity="ProductOneToOneBidirectionalEntity", mappedBy="product")
     * @ORM\JoinColumn(nullable=true)
     */
    protected ProductOneToOneBidirectionalEntity $oneToOneBidirectionalEntity;

    /**
     * @ORM\OneToOne(targetEntity="ExtendedProduct")
     * @ORM\JoinColumn(nullable=true, name="oneToOneSelfReferencing_id", referencedColumnName="id")
     */
    protected ExtendedProduct $oneToOneSelfReferencingEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductOneToManyBidirectionalEntity>
     * @ORM\OneToMany(targetEntity="ProductOneToManyBidirectionalEntity", mappedBy="product")
     */
    protected Collection $oneToManyBidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity>
     * @ORM\ManyToMany(targetEntity="Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity")
     * @ORM\JoinTable(name="products_oneToManyUnidirectionalWithJoinTableEntity",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="oneToManyUnidirectionalWithJoinTableEntity_id", referencedColumnName="id", unique=true)}
     *      )
     */
    protected Collection $oneToManyUnidirectionalWithJoinTableEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct>
     * @ORM\OneToMany(targetEntity="ExtendedProduct", mappedBy="oneToManySelfReferencingInverseEntity")
     */
    protected Collection $oneToManySelfReferencingEntities;

    /**
     * @ORM\ManyToOne(targetEntity="ExtendedProduct", inversedBy="oneToManySelfReferencingEntities")
     * @ORM\JoinColumn(nullable=true, name="oneToManySelfReferencingParent_id", referencedColumnName="id")
     */
    protected ExtendedProduct $oneToManySelfReferencingInverseEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity>
     * @ORM\ManyToMany(targetEntity="Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity")
     * @ORM\JoinTable(name="products_manyToManyUnidirectionalEntity",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="manyToManyUnidirectionalEntity_id", referencedColumnName="id")}
     *      )
     */
    protected Collection $manyToManyUnidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductManyToManyBidirectionalEntity>
     * @ORM\ManyToMany(targetEntity="ProductManyToManyBidirectionalEntity", inversedBy="products")
     * @ORM\JoinTable(name="products_manyToManyBidirectionalEntity")
     */
    protected Collection $manyToManyBidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct>
     * @ORM\ManyToMany(targetEntity="ExtendedProduct", mappedBy="manyToManySelfReferencingInverseEntities")
     */
    protected Collection $manyToManySelfReferencingEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct>
     * @ORM\ManyToMany(targetEntity="ExtendedProduct", inversedBy="manyToManySelfReferencingEntities")
     * @ORM\JoinTable(name="products_manyToManySelfReferencing",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="manyToManySelfReferencing_id", referencedColumnName="id")}
     *      )
     */
    protected Collection $manyToManySelfReferencingInverseEntities;

    public function __construct()
    {
        parent::__construct();

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
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductOneToOneBidirectionalEntity
     */
    public function getOneToOneBidirectionalEntity(): ProductOneToOneBidirectionalEntity
    {
        return $this->oneToOneBidirectionalEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductOneToOneBidirectionalEntity $oneToOneBidirectionalEntity
     */
    public function setOneToOneBidirectionalEntity(
        ProductOneToOneBidirectionalEntity $oneToOneBidirectionalEntity,
    ): void {
        $oneToOneBidirectionalEntity->setProduct($this);
        $this->oneToOneBidirectionalEntity = $oneToOneBidirectionalEntity;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct
     */
    public function getOneToOneSelfReferencingEntity(): self
    {
        return $this->oneToOneSelfReferencingEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct $oneToOneSelfReferencingEntity
     */
    public function setOneToOneSelfReferencingEntity(self $oneToOneSelfReferencingEntity): void
    {
        $this->oneToOneSelfReferencingEntity = $oneToOneSelfReferencingEntity;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductOneToManyBidirectionalEntity[]
     */
    public function getOneToManyBidirectionalEntities(): array
    {
        return $this->oneToManyBidirectionalEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductOneToManyBidirectionalEntity $oneToManyBidirectionalEntity
     */
    public function addOneToManyBidirectionalEntity(
        ProductOneToManyBidirectionalEntity $oneToManyBidirectionalEntity,
    ): void {
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
    public function addOneToManyUnidirectionalWithJoinTableEntity(
        UnidirectionalEntity $oneToManyUnidirectionalWithJoinTableEntity,
    ): void {
        $this->oneToManyUnidirectionalWithJoinTableEntities->add($oneToManyUnidirectionalWithJoinTableEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct[]
     */
    public function getOneToManySelfReferencingEntities(): array
    {
        return $this->oneToManySelfReferencingEntities->getValues();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct
     */
    public function getOneToManySelfReferencingInverseEntity(): self
    {
        return $this->oneToManySelfReferencingInverseEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct $oneToManySelfReferencing
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
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductManyToManyBidirectionalEntity[]
     */
    public function getManyToManyBidirectionalEntities(): array
    {
        return $this->manyToManyBidirectionalEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ProductManyToManyBidirectionalEntity $manyToManyBidirectionalEntity
     */
    public function addManyToManyBidirectionalEntity(
        ProductManyToManyBidirectionalEntity $manyToManyBidirectionalEntity,
    ): void {
        $manyToManyBidirectionalEntity->addProduct($this);
        $this->manyToManyBidirectionalEntities->add($manyToManyBidirectionalEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct[]
     */
    public function getManyToManySelfReferencingEntities(): array
    {
        return $this->manyToManySelfReferencingEntities->getValues();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct[]
     */
    public function getManyToManySelfReferencingInverseEntities(): array
    {
        return $this->manyToManySelfReferencingInverseEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct $manyToManySelfReferencing
     */
    public function addManyToManySelfReferencingEntity(self $manyToManySelfReferencing): void
    {
        $manyToManySelfReferencing->manyToManySelfReferencingInverseEntities->add($this);
        $this->manyToManySelfReferencingEntities->add($manyToManySelfReferencing);
    }

    /**
     * @return string|null
     */
    public function getStringField(): ?string
    {
        return $this->stringField;
    }

    /**
     * @param string|null $stringField
     */
    public function setStringField(?string $stringField): void
    {
        $this->stringField = $stringField;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProductTranslation
     */
    protected function createTranslation(): ExtendedProductTranslation
    {
        return new ExtendedProductTranslation();
    }
}
