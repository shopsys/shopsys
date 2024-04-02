<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class MainBlogCategoryUrlQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    /**
     * @return string|null
     */
    public function mainBlogCategoryUrlQuery(): ?string
    {
        $mainBlogCategoryId = $this->blogCategoryFacade->findVisibleMainBlogCategoryIdOnCurrentDomain();

        if ($mainBlogCategoryId === null) {
            return null;
        }

        try {
            return $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityIdOnCurrentDomain(
                'front_blogcategory_detail',
                $mainBlogCategoryId,
            );
        } catch (FriendlyUrlNotFoundException) {
            return null;
        }
    }
}
