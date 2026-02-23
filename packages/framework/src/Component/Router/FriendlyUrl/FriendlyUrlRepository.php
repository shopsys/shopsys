<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Store\Store;

class FriendlyUrlRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    protected function getFriendlyUrlRepository(): EntityRepository
    {
        return $this->em->getRepository(FriendlyUrl::class);
    }

    public function findByDomainIdAndSlug(int $domainId, string $slug): ?FriendlyUrl
    {
        return $this->getFriendlyUrlRepository()->findOneBy(
            [
                'domainId' => $domainId,
                'slug' => FriendlyUrlSlugNormalizer::normalize($slug),
            ],
        );
    }

    public function getMainFriendlyUrl(int $domainId, string $routeName, int $entityId): FriendlyUrl
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

    public function findMainFriendlyUrl(int $domainId, string $routeName, int $entityId): ?FriendlyUrl
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
     * @return array<int, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null>
     */
    public function getMainFriendlyUrlsIndexedByDomains(string $routeName, int $entityId): array
    {
        return $this->em->createQueryBuilder()
            ->select('f')
            ->from(FriendlyUrl::class, 'f', 'f.domainId')
            ->andWhere('f.routeName = :routeName')
            ->setParameter('routeName', $routeName)
            ->andWhere('f.entityId = :entityId')
            ->setParameter('entityId', $entityId)
            ->andWhere('f.main = TRUE')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityId(string $routeName, int $entityId): array
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
     * @param int[] $domainIds
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameDomainIdsAndEntityIds(string $routeName, int $entityId, array $domainIds): array
    {
        $criteria = [
            'routeName' => $routeName,
            'entityId' => $entityId,
            'domainId' => $domainIds,
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
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityIdAndDomainId(string $routeName, int $entityId, int $domainId): array
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
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getMainFriendlyUrlsByEntitiesIndexedByEntityId(
        array $entitiesOrEntityIds,
        string $routeName,
        int $domainId,
    ): array {
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
                ->andWhere('NORMALIZED(fu.slug) LIKE NORMALIZED(:text)');
            $querySearchText = $this->databaseSearchingHelper->getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

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
            'front_category_seo' => $this->entityNameResolver->resolve(ReadyCategorySeoMix::class),
        ];
    }
}
