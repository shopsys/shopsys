<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\ExtendedCategory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Tests\App\Functional\EntityExtension\Model\Category\Category;
use Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="categories")
 */
class ExtendedCategory extends Category
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
     * @ORM\OneToOne(targetEntity="CategoryOneToOneBidirectionalEntity", mappedBy="category")
     * @ORM\JoinColumn(nullable=true)
     */
    protected CategoryOneToOneBidirectionalEntity $oneToOneBidirectionalEntity;

    /**
     * @ORM\OneToOne(targetEntity="ExtendedCategory")
     * @ORM\JoinColumn(nullable=true, name="oneToOneSelfReferencing_id", referencedColumnName="id")
     */
    protected ExtendedCategory $oneToOneSelfReferencingEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryOneToManyBidirectionalEntity>
     * @ORM\OneToMany(targetEntity="CategoryOneToManyBidirectionalEntity", mappedBy="category")
     */
    protected Collection $oneToManyBidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity>
     * @ORM\ManyToMany(targetEntity="Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity")
     * @ORM\JoinTable(name="categories_oneToManyUnidirectionalWithJoinTableEntity",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="oneToManyUnidirectionalWithJoinTableEntity_id", referencedColumnName="id", unique=true)}
     *      )
     */
    protected Collection $oneToManyUnidirectionalWithJoinTableEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory>
     * @ORM\OneToMany(targetEntity="ExtendedCategory", mappedBy="oneToManySelfReferencingInverseEntity")
     */
    protected Collection $oneToManySelfReferencingEntities;

    /**
     * @ORM\ManyToOne(targetEntity="ExtendedCategory", inversedBy="oneToManySelfReferencingEntities")
     * @ORM\JoinColumn(nullable=true, name="oneToManySelfReferencingParent_id", referencedColumnName="id")
     */
    protected ExtendedCategory $oneToManySelfReferencingInverseEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity>
     * @ORM\ManyToMany(targetEntity="Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity")
     * @ORM\JoinTable(name="categories_manyToManyUnidirectionalEntity",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="manyToManyUnidirectionalEntity_id", referencedColumnName="id")}
     *      )
     */
    protected Collection $manyToManyUnidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryManyToManyBidirectionalEntity>
     * @ORM\ManyToMany(targetEntity="CategoryManyToManyBidirectionalEntity", inversedBy="categories")
     * @ORM\JoinTable(name="categories_manyToManyBidirectionalEntity")
     */
    protected Collection $manyToManyBidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory>
     * @ORM\ManyToMany(targetEntity="ExtendedCategory", mappedBy="manyToManySelfReferencingInverseEntities")
     */
    protected Collection $manyToManySelfReferencingEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory>
     * @ORM\ManyToMany(targetEntity="ExtendedCategory", inversedBy="manyToManySelfReferencingEntities")
     * @ORM\JoinTable(name="categories_manyToManySelfReferencing",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
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
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryOneToOneBidirectionalEntity
     */
    public function getOneToOneBidirectionalEntity(): CategoryOneToOneBidirectionalEntity
    {
        return $this->oneToOneBidirectionalEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryOneToOneBidirectionalEntity $oneToOneBidirectionalEntity
     */
    public function setOneToOneBidirectionalEntity(
        CategoryOneToOneBidirectionalEntity $oneToOneBidirectionalEntity,
    ): void {
        $oneToOneBidirectionalEntity->setCategory($this);
        $this->oneToOneBidirectionalEntity = $oneToOneBidirectionalEntity;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory
     */
    public function getOneToOneSelfReferencingEntity(): self
    {
        return $this->oneToOneSelfReferencingEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory $oneToOneSelfReferencing
     */
    public function setOneToOneSelfReferencingEntity(self $oneToOneSelfReferencing): void
    {
        $this->oneToOneSelfReferencingEntity = $oneToOneSelfReferencing;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryOneToManyBidirectionalEntity[]
     */
    public function getOneToManyBidirectionalEntities(): array
    {
        return $this->oneToManyBidirectionalEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryOneToManyBidirectionalEntity $oneToManyBidirectionalEntity
     */
    public function addOneToManyBidirectionalEntity(
        CategoryOneToManyBidirectionalEntity $oneToManyBidirectionalEntity,
    ): void {
        $oneToManyBidirectionalEntity->setCategory($this);
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
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory[]
     */
    public function getOneToManySelfReferencingEntities(): array
    {
        return $this->oneToManySelfReferencingEntities->getValues();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory
     */
    public function getOneToManySelfReferencingInverseEntity(): self
    {
        return $this->oneToManySelfReferencingInverseEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory $oneToManySelfReferencing
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
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryManyToManyBidirectionalEntity[]
     */
    public function getManyToManyBidirectionalEntities(): array
    {
        return $this->manyToManyBidirectionalEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\CategoryManyToManyBidirectionalEntity $manyToManyBidirectionalEntity
     */
    public function addManyToManyBidirectionalEntity(
        CategoryManyToManyBidirectionalEntity $manyToManyBidirectionalEntity,
    ): void {
        $manyToManyBidirectionalEntity->addCategory($this);
        $this->manyToManyBidirectionalEntities->add($manyToManyBidirectionalEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory[]
     */
    public function getManyToManySelfReferencingEntities(): array
    {
        return $this->manyToManySelfReferencingEntities->getValues();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory[]
     */
    public function getManyToManySelfReferencingInverseEntities(): array
    {
        return $this->manyToManySelfReferencingInverseEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory\ExtendedCategory $manyToManySelfReferencing
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
}
