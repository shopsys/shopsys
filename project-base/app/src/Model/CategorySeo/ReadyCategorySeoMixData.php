<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

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
     * @var \App\Model\Category\Category|null
     */
    public $category;

    /**
     * @var \App\Model\Product\Flag\Flag|null
     */
    public $flag;

    /**
     * @var string|null
     */
    public $ordering;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValue[]
     */
    public $readyCategorySeoMixParameterParameterValues = [];

    /**
     * @var string|null
     */
    public $h1;

    /**
     * @var string|null
     */
    public $shortDescription;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $title;

    /**
     * @var string|null
     */
    public $metaDescription;

    /**
     * @var bool
     */
    public bool $showInCategory = false;

    /**
     * @var string|null
     */
    public $choseCategorySeoMixCombinationJson;

    public UrlListData $urls;

    public ?string $categorySeoFilterFormTypeAllQueriesJson = null;
}
