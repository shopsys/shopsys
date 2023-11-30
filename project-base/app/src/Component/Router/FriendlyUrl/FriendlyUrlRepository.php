<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use App\Model\Article\Article;
use App\Model\Blog\Article\BlogArticle;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Product;
use App\Model\SeoPage\SeoPage;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository as BaseFriendlyUrlRepository;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Store\Store;

/**
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl|null findByDomainIdAndSlug(int $domainId, string $slug)
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl getMainFriendlyUrl(int $domainId, string $routeName, int $entityId)
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl|null findMainFriendlyUrl(int $domainId, string $routeName, int $entityId)
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl[] getAllByRouteNameAndEntityId(string $routeName, int $entityId)
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl[] getAllByRouteNameAndEntityIdAndDomainId(string $routeName, int $entityId, int $domainId)
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl[] getMainFriendlyUrlsByEntitiesIndexedByEntityId(object[]|int[] $entitiesOrEntityIds, string $routeName, int $domainId)
 */
class FriendlyUrlRepository extends BaseFriendlyUrlRepository
{
    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData|null $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNonUsedFriendlyUrlQueryBuilderByDomainIdAndQuickSearch(
        int $domainId,
        ?QuickSearchFormData $quickSearchData = null,
    ): QueryBuilder {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder->select('fu')
            ->from(FriendlyUrl::class, 'fu')
            ->where('fu.domainId = :domainId')
            ->andWhere($this->getWhereConditionForNonExistingEntities())
            ->setParameter('domainId', $domainId);

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder
                ->andWhere('NORMALIZE(fu.slug) LIKE NORMALIZE(:text)');
            $querySearchText = DatabaseSearching::getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

    /**
     * @return array<string, string>
     */
    public function getRouteNameToEntityMap(): array
    {
        return [
            'front_article_detail' => Article::class,
            'front_blogarticle_detail' => BlogArticle::class,
            'front_blogcategory_detail' => BlogCategory::class,
            'front_brand_detail' => Brand::class,
            'front_category_seo' => ReadyCategorySeoMix::class,
            'front_product_detail' => Product::class,
            'front_product_list' => Category::class,
            'front_stores_detail' => Store::class,
            'front_flag_detail' => Flag::class,
            'front_page_seo' => SeoPage::class,
        ];
    }

    /**
     * @return \Doctrine\ORM\Query\Expr\Orx
     */
    private function getWhereConditionForNonExistingEntities(): Orx
    {
        $queryBuilder = $this->em->createQueryBuilder();

        $expressionParts = [];
        $i = 0;

        foreach ($this->getRouteNameToEntityMap() as $routeName => $entity) {
            $tmpTableAlias = 'tmp' . $i;
            $expressionParts[] = $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('fu.routeName', "'{$routeName}'"),
                $queryBuilder->expr()->not(
                    $queryBuilder->expr()->exists(
                        $this->em->createQueryBuilder()->select($tmpTableAlias . '.id')
                            ->from($entity, $tmpTableAlias)
                            ->where('fu.entityId = ' . $tmpTableAlias . '.id')
                            ->getDQL(),
                    ),
                ),
            );
            $i++;
        }

        return $queryBuilder->expr()->orX(...$expressionParts);
    }

    /**
     * @return array<int, string>
     */
    public function getAllRouteNames(): array
    {
        $results = $this->em->createQueryBuilder()
            ->select('fu.routeName')
            ->from(FriendlyUrl::class, 'fu')
            ->groupBy('fu.routeName')
            ->orderBy('fu.routeName')
            ->getQuery()
            ->getScalarResult();

        return array_map(
            function ($row) {
                return $row['routeName'];
            },
            $results,
        );
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return string[]
     */
    public function getAllSlugsByRouteNameAndDomainId(int $domainId, string $routeName, int $entityId): array
    {
        $friendlyUrls = $this->getFriendlyUrlRepository()->findBy([
            'domainId' => $domainId,
            'routeName' => $routeName,
            'entityId' => $entityId,
        ]);

        return array_map(fn (FriendlyUrl $friendlyUrl) => $friendlyUrl->getSlug(), $friendlyUrls);
    }
}
