<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\Model\SeoPage\SeoPage;
use App\Model\SeoPage\SeoPageFacade;
use GraphQL\Executor\Promise\Promise;
use Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesQuery;

class SeoPageImagesQuery extends ImagesQuery
{
    /**
     * @param \App\Model\SeoPage\SeoPage $seoPage
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function ogImageBySeoPageQuery(SeoPage $seoPage): Promise
    {
        return $this->mainImageByEntityPromiseQuery(
            $seoPage,
            SeoPageFacade::IMAGE_TYPE_OG,
        );
    }
}
