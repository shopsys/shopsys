<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesQuery;

class MainBlogCategoryDataQuery extends AbstractQuery
{
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImagesQuery $imagesQuery,
    ) {
    }

    public function mainBlogCategoryDataQuery(): MainBlogCategoryData
    {
        $mainBlogCategoryData = new MainBlogCategoryData();

        $mainBlogCategory = $this->blogCategoryFacade->findVisibleMainBlogCategoryOnCurrentDomain();

        if ($mainBlogCategory === null) {
            return $mainBlogCategoryData;
        }

        try {
            $mainBlogCategoryId = $mainBlogCategory->getId();
            $mainBlogCategoryData->mainBlogCategoryName = $mainBlogCategory->getName($this->domain->getLocale());
            $mainBlogCategoryData->mainBlogCategoryUrl = $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityIdOnCurrentDomain(
                'front_blogcategory_detail',
                $mainBlogCategoryId,
            );
            $mainBlogCategoryData->mainBlogCategoryMainImage = $this->imagesQuery->mainImageByEntityIdPromiseQuery(
                $mainBlogCategoryId,
                'blogCategory',
                null,
            );

            return $mainBlogCategoryData;
        } catch (FriendlyUrlNotFoundException) {
            return $mainBlogCategoryData;
        }
    }
}
