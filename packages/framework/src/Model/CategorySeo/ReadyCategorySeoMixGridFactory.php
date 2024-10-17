<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Model\Category\CategoryTranslation;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation;

class ReadyCategorySeoMixGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(int $domainId, string $locale): Grid
    {
        $queryBuilder = $this->getAllByDomainIdQueryBuilder($domainId, $locale);

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'rcsmId');

        $grid = $this->gridFactory->create('ready_category_seo_mix', $dataSource);

        $grid->addColumn('categoryName', 'categoryName', t('Category name'));
        $grid->addColumn('friendlyUrlSlug', 'fuSlug', t('Main URL'));
        $grid->addColumn('parameters', 'rcsm.choseCategorySeoMixCombinationJson', t('Combination of parameters and their values'));
        $grid->addColumn('flagName', 'flagName', t('Flag'));
        $grid->addColumn('ordering', 'rcsm.ordering', t('Ordering'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_categoryseo_readycombination', [
            'categoryId' => 'categoryId',
            'choseCategorySeoMixCombinationJson' => 'rcsm.choseCategorySeoMixCombinationJson',
        ]);
        $grid->addDeleteActionColumn('admin_categoryseo_delete', ['id' => 'rcsmId']);

        $grid->setTheme('@ShopsysFramework/Admin/Content/CategorySeo/listGrid.html.twig');

        $grid->enablePaging();

        return $grid;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllByDomainIdQueryBuilder(int $domainId, string $locale): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('rcsm.id as rcsmId, c.id as categoryId, ct.name as categoryName, fu.slug as fuSlug, rcsm.choseCategorySeoMixCombinationJson, ft.name as flagName, rcsm.ordering')
            ->from(ReadyCategorySeoMix::class, 'rcsm')
            ->andWhere('rcsm.domainId = :domainId')
            ->join('rcsm.category', 'c')
            ->leftJoin(CategoryTranslation::class, 'ct', Join::WITH, 'ct.translatable = c and ct.locale = :locale ')
            ->leftJoin(FriendlyUrl::class, 'fu', Join::WITH, 'fu.routeName = :routeName and fu.entityId = rcsm.id and fu.domainId = :domainId and fu.main = true')
            ->leftJoin('rcsm.flag', 'f')
            ->leftJoin(FlagTranslation::class, 'ft', Join::WITH, 'ft.translatable = f and ft.locale = :locale')
            ->setParameter('locale', $locale)
            ->setParameter('domainId', $domainId)
            ->setParameter('routeName', 'front_category_seo')
            ->orderBy('rcsm.id', 'DESC');
    }
}
