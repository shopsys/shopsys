<?php

declare(strict_types=1);

namespace App\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="categories")
 * @ORM\Entity
 * @property \App\Model\Category\Category|null $parent
 * @property \App\Model\Category\Category[]|\Doctrine\Common\Collections\Collection $children
 * @method \App\Model\Category\Category|null getParent()
 * @method \App\Model\Category\Category[] getChildren()
 * @method setParent(\App\Model\Category\Category|null $parent)
 * @method setTranslations(\App\Model\Category\CategoryData $categoryData)
 * @property \App\Model\Category\CategoryDomain[]|\Doctrine\Common\Collections\Collection $domains
 * @method \App\Model\Category\CategoryDomain getCategoryDomain(int $domainId)
 */
class Category extends BaseCategory
{
    private const CATEGORY_LEVEL_0 = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     */
    protected $akeneoCode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $svgIcon;

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    public function __construct(BaseCategoryData $categoryData)
    {
        parent::__construct($categoryData);

        $this->akeneoCode = $categoryData->akeneoCode;
        $this->svgIcon = $categoryData->svgIcon;
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    public function edit(BaseCategoryData $categoryData)
    {
        parent::edit($categoryData);

        $this->svgIcon = $categoryData->svgIcon;
    }

    /**
     * @return string|null
     */
    public function getAkeneoCode(): ?string
    {
        return $this->akeneoCode;
    }

    /**
     * @return \App\Model\Category\Category[]
     */
    public function getParentsWithoutRootCategory(): array
    {
        if ($this->parent === null || $this->parent->getLevel() === self::CATEGORY_LEVEL_0) {
            return [];
        }

        return array_merge([$this->parent], $this->parent->getParentsWithoutRootCategory());
    }

    /**
     * @return string|null
     */
    public function getSvgIcon(): ?string
    {
        return $this->svgIcon;
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescription(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getShortDescription();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getProductSeriesListTitle(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getProductSeriesListTitle();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getProductSeriesListDescription(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getProductSeriesListDescription();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getProductSeriesListLink(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getProductSeriesListLink();
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    protected function setDomains(BaseCategoryData $categoryData): void
    {
        parent::setDomains($categoryData);

        foreach ($this->domains as $categoryDomain) {
            $domainId = $categoryDomain->getDomainId();
            $categoryDomain->setShortDescription($categoryData->shortDescription[$domainId]);
            $categoryDomain->setProductSeriesListTitle($categoryData->productSeriesListTitle[$domainId]);
            $categoryDomain->setProductSeriesListDescription($categoryData->productSeriesListDescription[$domainId]);
            $categoryDomain->setProductSeriesListLink($categoryData->productSeriesListLink[$domainId]);
        }
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    protected function createDomains(BaseCategoryData $categoryData): void
    {
        $domainIds = array_keys($categoryData->seoTitles);

        foreach ($domainIds as $domainId) {
            $categoryDomain = new CategoryDomain($this, $domainId);
            $this->domains->add($categoryDomain);
        }

        $this->setDomains($categoryData);
    }

    /**
     * @return \App\Model\Category\CategoryDomain[]|\Doctrine\Common\Collections\Collection
     */
    public function getCategoryDomains()
    {
        return $this->domains;
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    protected function setData(BaseCategoryData $categoryData): void
    {
        parent::setData($categoryData);
    }
}
