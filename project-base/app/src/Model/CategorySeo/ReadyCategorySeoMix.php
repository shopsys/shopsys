<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

/**
 * @ORM\Table(
 *     name="ready_category_seo_mixes",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="chose_category_seo_mix_combination_json", columns={"chose_category_seo_mix_combination_json"})
 *     }
 * )
 * @ORM\Entity
 */
class ReadyCategorySeoMix
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $domainId;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $choseCategorySeoMixCombinationJson;

    /**
     * @var \App\Model\Category\Category
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category")
     * @ORM\JoinColumn(nullable=false, name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;

    /**
     * @var \App\Model\Product\Flag\Flag|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Flag\Flag")
     * @ORM\JoinColumn(nullable=true, name="flag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $flag;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $ordering;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValue>
     * @ORM\OneToMany(targetEntity="App\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValue", mappedBy="readyCategorySeoMix", cascade={"persist" ,"remove"}, fetch="EXTRA_LAZY")
     */
    private $readyCategorySeoMixParameterParameterValues;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $h1;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $shortDescription;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $title;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $metaDescription;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private bool $showInCategory;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    private string $uuid;

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     */
    public function __construct(ReadyCategorySeoMixData $readyCategorySeoMixData)
    {
        $this->readyCategorySeoMixParameterParameterValues = new ArrayCollection();

        $this->category = $readyCategorySeoMixData->category;
        $this->flag = $readyCategorySeoMixData->flag;
        $this->ordering = $readyCategorySeoMixData->ordering;
        $this->domainId = $readyCategorySeoMixData->domainId;
        $this->choseCategorySeoMixCombinationJson = $readyCategorySeoMixData->choseCategorySeoMixCombinationJson;

        $this->h1 = $readyCategorySeoMixData->h1;
        $this->shortDescription = $readyCategorySeoMixData->shortDescription;
        $this->description = $readyCategorySeoMixData->description;
        $this->title = $readyCategorySeoMixData->title;
        $this->metaDescription = $readyCategorySeoMixData->metaDescription;
        $this->showInCategory = $readyCategorySeoMixData->showInCategory;

        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     */
    public function edit(ReadyCategorySeoMixData $readyCategorySeoMixData): void
    {
        $this->category = $readyCategorySeoMixData->category;
        $this->flag = $readyCategorySeoMixData->flag;
        $this->ordering = $readyCategorySeoMixData->ordering;

        $this->h1 = $readyCategorySeoMixData->h1;
        $this->shortDescription = $readyCategorySeoMixData->shortDescription;
        $this->description = $readyCategorySeoMixData->description;
        $this->title = $readyCategorySeoMixData->title;
        $this->metaDescription = $readyCategorySeoMixData->metaDescription;
        $this->showInCategory = $readyCategorySeoMixData->showInCategory;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Model\Category\Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return \App\Model\Product\Flag\Flag|null
     */
    public function getFlag(): ?Flag
    {
        return $this->flag;
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     */
    public function setFlag(Flag $flag): void
    {
        $this->flag = $flag;
    }

    /**
     * @return string
     */
    public function getOrdering(): string
    {
        return $this->ordering;
    }

    /**
     * @param string $ordering
     */
    public function setOrdering(string $ordering): void
    {
        $this->ordering = $ordering;
    }

    /**
     * @return \App\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValue[]
     */
    public function getReadyCategorySeoMixParameterParameterValues(): array
    {
        return $this->readyCategorySeoMixParameterParameterValues->getValues();
    }

    /**
     * @return string
     */
    public function getH1(): string
    {
        return $this->h1;
    }

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return bool
     */
    public function showInCategory(): bool
    {
        return $this->showInCategory;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }
}
