<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Shopsys\FrameworkBundle\Model\Store\Store;

class FriendlyUrlRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getFriendlyUrlRepository()
    {
        return $this->em->getRepository(FriendlyUrl::class);
    }

    /**
     * @param int $domainId
     * @param string $slug
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function findByDomainIdAndSlug($domainId, $slug)
    {
        return $this->getFriendlyUrlRepository()->findOneBy(
            [
                'domainId' => $domainId,
                'slug' => $slug,
            ],
        );
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
     */
    public function getMainFriendlyUrl($domainId, $routeName, $entityId)
    {
        $criteria = [
            'domainId' => $domainId,
            'routeName' => $routeName,
            'entityId' => $entityId,
            'main' => true,
        ];
        $friendlyUrl = $this->getFriendlyUrlRepository()->findOneBy($criteria);

        if ($friendlyUrl === null) {
            throw new FriendlyUrlNotFoundException();
        }

        return $friendlyUrl;
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function findMainFriendlyUrl($domainId, $routeName, $entityId)
    {
        $criteria = [
            'domainId' => $domainId,
            'routeName' => $routeName,
            'entityId' => $entityId,
            'main' => true,
        ];

        return $this->getFriendlyUrlRepository()->findOneBy($criteria);
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityId($routeName, $entityId)
    {
        $criteria = [
            'routeName' => $routeName,
            'entityId' => $entityId,
        ];

        return $this->getFriendlyUrlRepository()->findBy(
            $criteria,
            [
                'domainId' => 'ASC',
                'slug' => 'ASC',
            ],
        );
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityIdAndDomainId($routeName, $entityId, $domainId)
    {
        $criteria = [
            'routeName' => $routeName,
            'entityId' => $entityId,
            'domainId' => $domainId,
        ];

        return $this->getFriendlyUrlRepository()->findBy($criteria);
    }

    /**
     * @param object[]|int[] $entitiesOrEntityIds
     * @param string $routeName
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getMainFriendlyUrlsByEntitiesIndexedByEntityId(array $entitiesOrEntityIds, $routeName, $domainId)
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('f')
            ->from(FriendlyUrl::class, 'f', 'f.entityId')
            ->andWhere('f.routeName = :routeName')->setParameter('routeName', $routeName)
            ->andWhere('f.entityId IN (:entities)')->setParameter('entities', $entitiesOrEntityIds)
            ->andWhere('f.domainId = :domainId')->setParameter('domainId', $domainId)
            ->andWhere('f.main = TRUE');

        return $queryBuilder->getQuery()->execute();
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

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNonUsedFriendlyUrlQueryBuilderByDomainIdAndQuickSearch(
        int $domainId,
        QuickSearchFormData $quickSearchData,
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
     * @return \Doctrine\ORM\Query\Expr\Orx
     */
    protected function getWhereConditionForNonExistingEntities(): Orx
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
     * @return array<string, string>
     */
    public function getRouteNameToEntityMap(): array
    {
        return [
            'front_article_detail' => $this->entityNameResolver->resolve(Article::class),
            'front_blogarticle_detail' => $this->entityNameResolver->resolve(BlogArticle::class),
            'front_blogcategory_detail' => $this->entityNameResolver->resolve(BlogCategory::class),
            'front_brand_detail' => $this->entityNameResolver->resolve(Brand::class),
            'front_product_detail' => $this->entityNameResolver->resolve(Product::class),
            'front_product_list' => $this->entityNameResolver->resolve(Category::class),
            'front_stores_detail' => $this->entityNameResolver->resolve(Store::class),
            'front_flag_detail' => $this->entityNameResolver->resolve(Flag::class),
            'front_page_seo' => $this->entityNameResolver->resolve(SeoPage::class),
        ];
    }
}
