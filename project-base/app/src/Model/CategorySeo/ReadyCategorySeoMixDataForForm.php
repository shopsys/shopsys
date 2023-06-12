<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class ReadyCategorySeoMixDataForForm
{
    public ?string $h1 = null;

    public ?string $shortDescription = null;

    public ?string $description = null;

    public ?string $title = null;

    public ?string $metaDescription = null;

    public ?string $categorySeoFilterFormTypeAllQueriesJson = null;

    public ?string $choseCategorySeoMixCombinationJson = null;

    public UrlListData $urls;

    public bool $showInCategory = false;
}
