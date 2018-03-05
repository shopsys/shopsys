<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Form\UrlListData;

class CategoryData
{
    /**
     * @var string[]
     */
    public $name;

    /**
     * @var string[]|null[]
     */
    public $seoTitles;

    /**
     * @var string[]|null[]
     */
    public $seoMetaDescriptions;

    /**
     * @var string[]|null[]
     */
    public $seoH1s;

    /**
     * @var string[]
     */
    public $descriptions;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category|null
     */
    public $parent;

    /**
     * @var int[]
     */
    public $hiddenOnDomains;

    /**
     * @var \Shopsys\FrameworkBundle\Form\UrlListData
     */
    public $urls;

    /**
     * @var string[]
     */
    public $image;

    /**
     * @var array
     */
    public $pluginData;

    public function __construct()
    {
        $this->name = [];
        $this->seoTitles = [];
        $this->seoMetaDescriptions = [];
        $this->seoH1s = [];
        $this->descriptions = [];
        $this->hiddenOnDomains = [];
        $this->urls = new UrlListData();
        $this->image = [];
        $this->pluginData = [];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryDomain[] $categoryDomains
     */
    public function setFromEntity(Category $category, array $categoryDomains)
    {
        $translations = $category->getTranslations();
        $names = [];
        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $this->name = $names;
        $this->parent = $category->getParent();
        $seoTitles = [];
        $seoMetaDescriptions = [];
        $seoH1 = [];
        $descriptions = [];
        $hiddenOnDomains = [];
        foreach ($categoryDomains as $categoryDomain) {
            $seoTitles[$categoryDomain->getDomainId()] = $categoryDomain->getSeoTitle();
            $seoMetaDescriptions[$categoryDomain->getDomainId()] = $categoryDomain->getSeoMetaDescription();
            $seoH1[$categoryDomain->getDomainId()] = $categoryDomain->getSeoH1();
            $descriptions[$categoryDomain->getDomainId()] = $categoryDomain->getDescription();
            if ($categoryDomain->isHidden()) {
                $hiddenOnDomains[] = $categoryDomain->getDomainId();
            }
        }
        $this->hiddenOnDomains = $hiddenOnDomains;
        $this->seoTitles = $seoTitles;
        $this->seoMetaDescriptions = $seoMetaDescriptions;
        $this->seoH1s = $seoH1;
        $this->descriptions = $descriptions;
    }
}
