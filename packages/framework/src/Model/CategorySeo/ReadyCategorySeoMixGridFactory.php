<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Category\CategoryTranslation;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation;

class ReadyCategorySeoMixGridFactory
{
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly EntityManagerInterface $em,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    /**
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     */
    public function create(int $domainId, string $locale): Grid
    {
        $queryBuilder = $this->getAllByDomainIdQueryBuilder($domainId, $locale);

        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'rcsmId');

        $grid = $this->gridFactory->create('ready_category_seo_mix', $dataSource, AdminRoleConstant::ROLE_CATEGORY_SEO);

        $grid->addColumn('categoryName', 'categoryName', t('Category name'));
        $grid->addColumn('friendlyUrlSlug', 'fuSlug', t('Main URL'));
        $grid->addColumn('parameters', 'rcsm.selectedCategorySeoMixCombinationJson', t('Combination of parameters and their values'));
        $grid->addColumn('flagName', 'flagName', t('Flag'));
        $grid->addColumn('ordering', 'rcsm.ordering', t('Ordering'));

        $grid->addEditActionColumn('admin_categoryseo_readycombination', [
            'categoryId' => 'categoryId',
            'selectedCategorySeoMixCombinationJson' => 'rcsm.selectedCategorySeoMixCombinationJson',
        ]);
        $grid->addDeleteActionColumn('admin_categoryseo_delete', ['id' => 'rcsmId']);

        $grid->setTheme('@ShopsysAdministration/content/categorySeo/listGrid.html.twig');

        $grid->enablePaging();

        return $grid;
    }

    public function getAllByDomainIdQueryBuilder(int $domainId, string $locale): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('rcsm.id as rcsmId, c.id as categoryId, ct.name as categoryName, fu.slug as fuSlug, rcsm.selectedCategorySeoMixCombinationJson, ft.name as flagName, rcsm.ordering')
            ->from(ReadyCategorySeoMix::class, 'rcsm')
            ->andWhere('rcsm.domainId = :domainId')
            ->join('rcsm.category', 'c')
            ->leftJoin(CategoryTranslation::class, 'ct', Join::WITH, 'ct.translatable = c and ct.locale = :locale')
            ->leftJoin(FriendlyUrl::class, 'fu', Join::WITH, 'fu.routeName = :routeName and fu.entityId = rcsm.id and fu.domainId = :domainId and fu.main = true')
            ->leftJoin('rcsm.flag', 'f')
            ->leftJoin(FlagTranslation::class, 'ft', Join::WITH, 'ft.translatable = f and ft.locale = :locale')
            ->setParameter('locale', $locale)
            ->setParameter('domainId', $domainId)
            ->setParameter('routeName', 'front_category_seo')
            ->orderBy('rcsm.id', 'DESC');
    }
}
