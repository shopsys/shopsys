<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesQuery;

class MainBlogCategoryDataQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesQuery $imagesQuery
     */
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImagesQuery $imagesQuery,
    ) {
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Resolver\Settings\MainBlogCategoryData
     */
    public function mainBlogCategoryDataQuery(): MainBlogCategoryData
    {
        $mainBlogCategoryData = new MainBlogCategoryData();

        $mainBlogCategoryId = $this->blogCategoryFacade->findVisibleMainBlogCategoryIdOnCurrentDomain();

        if ($mainBlogCategoryId === null) {
            return $mainBlogCategoryData;
        }

        try {
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
