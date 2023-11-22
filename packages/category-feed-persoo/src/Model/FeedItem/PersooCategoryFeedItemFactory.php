<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\PersooBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class PersooCategoryFeedItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImageFacade $imageFacade,
        protected readonly CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\CategoryFeed\PersooBundle\Model\FeedItem\PersooCategoryFeedItem
     */
    public function create(Category $category, DomainConfig $domainConfig): PersooCategoryFeedItem
    {
        $locale = $domainConfig->getLocale();
        $hierarchyIds = [$category->getId()];
        $hierarchyNames = [$category->getName($locale)];
        $parent = $category->getParent();
        $rootCategory = $this->categoryRepository->getRootCategory();

        try {
            $imageUrl = $this->imageFacade->getImageUrl($domainConfig, $category);
        } catch (ImageNotFoundException) {
            $imageUrl = null;
        }

        while ($parent !== null && $parent !== $rootCategory) {
            $hierarchyIds[] = $parent->getId();
            $hierarchyNames[] = $parent->getName($locale);
            $parent = $parent->getParent();
        }

        return new PersooCategoryFeedItem(
            $category->getId(),
            $category->getName($locale),
            array_reverse($hierarchyIds),
            $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId($domainConfig->getId(), 'front_product_list', $category->getId()),
            array_reverse($hierarchyNames),
            $category->getDescription($domainConfig->getId()),
            $imageUrl,
        );
    }
}
