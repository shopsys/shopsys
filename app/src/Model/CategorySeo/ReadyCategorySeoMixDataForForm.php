<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

class ReadyCategorySeoMixDataForForm
{
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
     * @var string|null
     */
    public $categorySeoFilterFormTypeAllQueriesJson;

    /**
     * @var string|null
     */
    public $choseCategorySeoMixCombinationJson;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public $urls;

    /**
     * @var bool
     */
    public bool $showInCategory = false;
}
