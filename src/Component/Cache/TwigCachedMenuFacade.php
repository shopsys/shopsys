<?php

declare(strict_types=1);

namespace App\Component\Cache;

use App\Model\Category\Category;
use App\Twig\Cache\TwigCacheFacade;

class TwigCachedMenuFacade
{
    public const HORIZONTAL_PANEL_MENU = 'HORIZONTAL_PANEL_MENU';
    public const MOBILE_PANEL_MENU = 'MOBILE_PANEL_MENU';
    public const MOBILE_SLIDING_MENU = 'MOBILE_SLIDING_MENU';

    /**
     * @var \App\Twig\Cache\TwigCacheFacade
     */
    private $twigCacheFacade;

    /**
     * @param \App\Twig\Cache\TwigCacheFacade $twigCacheFacade
     */
    public function __construct(
        TwigCacheFacade $twigCacheFacade
    ) {
        $this->twigCacheFacade = $twigCacheFacade;
    }

    /**
     * @param int $domainId
     */
    public function invalidateCachedMenuByDomainId(int $domainId): void
    {
        $this->twigCacheFacade->invalidateByKey(self::HORIZONTAL_PANEL_MENU, $domainId);
        $this->twigCacheFacade->invalidateByKey(self::MOBILE_PANEL_MENU, $domainId);
        $this->twigCacheFacade->invalidateByKey(self::MOBILE_SLIDING_MENU, $domainId);
    }

    /**
     * @param \App\Model\Category\Category $category
     */
    public function invalidateCachedMenuByCategory(Category $category): void
    {
        foreach ($category->getCategoryDomains() as $categoryDomain) {
            $this->invalidateCachedMenuByDomainId($categoryDomain->getDomainId());
        }
    }
}
