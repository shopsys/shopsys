<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="categories")
 * @ORM\Entity
 * @property \Shopsys\ShopBundle\Model\Category\Category|null $parent
 * @property \Shopsys\ShopBundle\Model\Category\Category[]|\Doctrine\Common\Collections\Collection $children
 * @method \Shopsys\ShopBundle\Model\Category\Category|null getParent()
 * @method \Shopsys\ShopBundle\Model\Category\Category[] getChildren()
 * @method setParent(\Shopsys\ShopBundle\Model\Category\Category|null $parent)
 * @method setTranslations(\Shopsys\ShopBundle\Model\Category\CategoryData $categoryData)
 * @method setDomains(\Shopsys\ShopBundle\Model\Category\CategoryData $categoryData)
 * @method createDomains(\Shopsys\ShopBundle\Model\Category\CategoryData $categoryData)
 */
class Category extends BaseCategory
{
    /**
     * @param \Shopsys\ShopBundle\Model\Category\CategoryData $categoryData
     */
    public function __construct(BaseCategoryData $categoryData)
    {
        parent::__construct($categoryData);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Category\CategoryData $categoryData
     */
    public function edit(BaseCategoryData $categoryData)
    {
        parent::edit($categoryData);
    }
}
