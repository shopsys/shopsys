<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\Exception\BlogCategoryNotFoundException;
use Shopsys\FrontendApiBundle\Model\Blog\Category\Exception\BlogCategoryNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BlogCategoryQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    public function blogCategoryByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): BlogCategory
    {
        try {
            $domainId = $this->domain->getId();

            if ($uuid !== null) {
                $blogCategory = $this->blogCategoryFacade->getVisibleByUuid($domainId, $uuid);
            } elseif ($urlSlug !== null) {
                $urlSlug = ltrim($urlSlug, '/');
                $blogCategory = $this->getVisibleOnDomainAndSlug($urlSlug);
            } else {
                throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
            }
        } catch (BlogCategoryNotFoundException $blogCategoryNotFoundException) {
            throw new BlogCategoryNotFoundUserError($blogCategoryNotFoundException->getMessage());
        }

        return $blogCategory;
    }

    /**
     * @param string $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    protected function getVisibleOnDomainAndSlug(string $urlSlug): BlogCategory
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_blogcategory_detail',
                $urlSlug,
            );

            return $this->blogCategoryFacade->getVisibleOnDomainById($this->domain->getId(), $friendlyUrl->getEntityId());
        } catch (FriendlyUrlNotFoundException|BlogCategoryNotFoundException $blogCategoryNotFoundException) {
            throw new BlogCategoryNotFoundUserError(sprintf('No visible blog category was found by slug "%s"', $urlSlug));
        }
    }
}
