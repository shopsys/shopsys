<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;

class SeoPageImagesQuery extends ImagesQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage $seoPage
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
