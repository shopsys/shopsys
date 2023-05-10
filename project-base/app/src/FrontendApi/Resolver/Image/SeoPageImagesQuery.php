<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\Model\SeoPage\SeoPage;
use App\Model\SeoPage\SeoPageFacade;
use GraphQL\Executor\Promise\Promise;

class SeoPageImagesQuery extends ImagesQuery
{
    /**
     * @param \App\Model\SeoPage\SeoPage $seoPage
     * @param string|null $size
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function ogImageBySeoPageQuery(SeoPage $seoPage, ?string $size): Promise
    {
        return $this->mainImageByEntityPromiseQuery(
            $seoPage,
            SeoPageFacade::IMAGE_TYPE_OG,
            $size
        );
    }
}
