<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use GraphQL\Executor\Promise\Promise;

class MainBlogCategoryData
{
    public ?string $mainBlogCategoryUrl = null;

    public ?Promise $mainBlogCategoryMainImage = null;
}
