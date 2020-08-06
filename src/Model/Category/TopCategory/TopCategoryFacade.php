<?php

declare(strict_types=1);

namespace App\Model\Category\TopCategory;

use App\Twig\Cache\TwigCacheFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade as BaseTopCategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryRepository;

/**
 * @method \App\Model\Category\Category[] getAllCategoriesByDomainId(int $domainId)
 * @method \App\Model\Category\Category[] getVisibleCategoriesByDomainId(int $domainId)
 * @method \App\Model\Category\Category[] getCategoriesFromTopCategories(\Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategory[] $topCategories)
 */
class TopCategoryFacade extends BaseTopCategoryFacade
{
    /**
     * @var \App\Twig\Cache\TwigCacheFacade
     */
    private TwigCacheFacade $twigCacheFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryRepository $topCategoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFactoryInterface $topCategoryFactory
     * @param \App\Twig\Cache\TwigCacheFacade $twigCacheFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        TopCategoryRepository $topCategoryRepository,
        TopCategoryFactoryInterface $topCategoryFactory,
        TwigCacheFacade $twigCacheFacade
    ) {
        parent::__construct($em, $topCategoryRepository, $topCategoryFactory);
        $this->twigCacheFacade = $twigCacheFacade;
    }

    /**
     * @param mixed $domainId
     * @param array $categories
     */
    public function saveTopCategoriesForDomain($domainId, array $categories)
    {
        parent::saveTopCategoriesForDomain($domainId, $categories);
        $this->twigCacheFacade->invalidateByKey($this->twigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE, $domainId);
    }
}
