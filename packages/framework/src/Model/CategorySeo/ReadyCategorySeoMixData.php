<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

class ReadyCategorySeoMixData
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category|null
     */
    public $category;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag|null
     */
    public $flag;

    /**
     * @var string|null
     */
    public $ordering;

    /**
     * @var \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValue[]
     */
    public $readyCategorySeoMixParameterParameterValues = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData
     */
    public $seo;

    /**
     * @var string|null
     */
    public $shortDescription;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var bool
     */
    public $showInCategory = false;

    /**
     * @var string|null
     */
    public $selectedCategorySeoMixCombinationJson;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public $urls;

    /**
     * @var string|null
     */
    public $categorySeoFilterFormTypeAllQueriesJson;
}
