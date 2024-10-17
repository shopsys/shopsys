<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

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
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $choseCategorySeoMixCombinationJson;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category")
     * @ORM\JoinColumn(nullable=false, name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $category;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Flag\Flag")
     * @ORM\JoinColumn(nullable=true, name="flag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $flag;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $ordering;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValue>
     * @ORM\OneToMany(
     *     targetEntity="Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValue",
     *     mappedBy="readyCategorySeoMix",
     *     cascade={"persist" ,"remove"},
     *     fetch="EXTRA_LAZY"
     * )
     */
    protected $readyCategorySeoMixParameterParameterValues;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $h1;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $shortDescription;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $title;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metaDescription;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $showInCategory;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
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
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag|null
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     */
    public function setFlag($flag)
    {
        $this->flag = $flag;
    }

    /**
     * @return string
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * @param string $ordering
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValue[]
     */
    public function getReadyCategorySeoMixParameterParameterValues()
    {
        return $this->readyCategorySeoMixParameterParameterValues->getValues();
    }

    /**
     * @return string
     */
    public function getH1()
    {
        return $this->h1;
    }

    /**
     * @return string|null
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return bool
     */
    public function showInCategory()
    {
        return $this->showInCategory;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string|null
     */
    public function getChoseCategorySeoMixCombinationJson()
    {
        return $this->choseCategorySeoMixCombinationJson;
    }
}
