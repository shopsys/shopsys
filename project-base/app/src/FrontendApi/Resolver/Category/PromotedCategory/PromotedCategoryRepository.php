<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category\PromotedCategory;

use App\Model\Category\CategoryRepository;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategory;

class PromotedCategoryRepository
{
    /**
     * @param \App\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(private CategoryRepository $categoryRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[]
     */
    public function getVisiblePromotedCategoriesOnDomain(DomainConfig $domainConfig): array
    {
        error_log("🔍 [PromotedCategories] Domain: {$domainConfig->getName()} (ID: {$domainConfig->getId()})");
        error_log("🔍 [PromotedCategories] Locale: {$domainConfig->getLocale()}");
        error_log("🔍 [PromotedCategories] URL: {$domainConfig->getUrl()}");
        
        $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        
        // Log the base query builder state
        error_log("🔍 [PromotedCategories] Base query builder created");
        
        $result = $queryBuilder
            ->addSelect('ct, cd')
            ->join(TopCategory::class, 'tc', Join::WITH, 'tc.category = c AND tc.domainId = :domainId')
            ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
            ->setParameter('locale', $domainConfig->getLocale())
            ->orderBy('tc.position')
            ->getQuery()->getResult();
        
        error_log("🔍 [PromotedCategories] Query result count: " . count($result));
        error_log("🔍 [PromotedCategories] Query parameters: domainId={$domainConfig->getId()}, locale={$domainConfig->getLocale()}");
        
        if (empty($result)) {
            error_log("⚠️ [PromotedCategories] EMPTY RESULT - This is the issue!");
        }
        
        return $result;
    }
}
