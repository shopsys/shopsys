<?php

declare(strict_types=1);

namespace App\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @property \App\Model\Category\Category|null $parent
 * @property \Doctrine\Common\Collections\Collection<int, \App\Model\Category\Category> $children
 * @method \App\Model\Category\Category|null getParent()
 * @method \App\Model\Category\Category[] getChildren()
 * @method void setParent(\App\Model\Category\Category|null $parent = null)
 * @method void setTranslations(\App\Model\Category\CategoryData $categoryData)
 * @method void setDomains(\App\Model\Category\CategoryData $categoryData)
 * @method void createDomains(\App\Model\Category\CategoryData $categoryData)
 * @method __construct(\App\Model\Category\CategoryData $categoryData)
 */
#[AsMcpTable]
#[ORM\Table(name: 'categories')]
#[ORM\Index(columns: ['lft'])]
#[ORM\Index(columns: ['rgt'])]
#[ORM\Entity]
#[Gedmo\Tree(type: 'nested')]
class Category extends BaseCategory
{
    private const CATEGORY_LEVEL_0 = 0;

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    #[Override]
    public function edit(BaseCategoryData $categoryData): void
    {
        parent::edit($categoryData);
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    #[Override]
    protected function setData(BaseCategoryData $categoryData): void
    {
        parent::setData($categoryData);
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
}
