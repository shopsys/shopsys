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
 * @method setDomains(\App\Model\Category\CategoryData $categoryData)
 * @method createDomains(\App\Model\Category\CategoryData $categoryData)
 */
class Category extends BaseCategory
{
    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    public function __construct(BaseCategoryData $categoryData)
    {
        parent::__construct($categoryData);
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    public function edit(BaseCategoryData $categoryData)
    {
        parent::edit($categoryData);
    }
}
