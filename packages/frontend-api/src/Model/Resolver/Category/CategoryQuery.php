<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\Exception\CategoryNotFoundUserError;

class CategoryQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function categoryByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): Category
    {
        if ($uuid !== null) {
            return $this->getByUuid($uuid);
        }

        if ($urlSlug !== null) {
            return $this->getVisibleOnDomainAndSlug($urlSlug);
        }

        throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    protected function getByUuid(string $uuid): Category
    {
        try {
            return $this->categoryFacade->getByUuid($uuid);
        } catch (CategoryNotFoundException $categoryNotFoundException) {
            throw new CategoryNotFoundUserError($categoryNotFoundException->getMessage());
        }
    }

    /**
     * @param string $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    protected function getVisibleOnDomainAndSlug(string $urlSlug): Category
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_product_list',
                $urlSlug,
            );

            return $this->categoryFacade->getVisibleOnDomainById($this->domain->getId(), $friendlyUrl->getEntityId());
        } catch (FriendlyUrlNotFoundException | CategoryNotFoundException) {
            throw new CategoryNotFoundUserError('Category with URL slug `' . $urlSlug . '` does not exist.');
        }
    }
}
