<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\OrderingEnum;
use Shopsys\AdministrationBundle\Component\Search\Filter;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Shopsys\AdministrationBundle\Component\Search\SearchConfig;
use Shopsys\AdministrationBundle\Model\Blog\Author\BlogArticleAuthorCrudHandler;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Blog\BlogArticleAuthorFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

#[CrudController(BlogArticleAuthor::class)]
class BlogArticleAuthorController extends AbstractCrudController
{
    protected const int ARTICLES_GRID_DEFAULT_LIMIT = 10;

    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
        protected readonly BlogArticleRepository $blogArticleRepository,
        protected readonly Localization $localization,
    ) {
    }

    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->registerHandler(BlogArticleAuthorCrudHandler::class)
            ->setMenuSection(SideMenuBuilder::SECTION_BLOG)
            ->setCustomRoleConstant(AdminRoleConstant::ROLE_BLOG_ARTICLE_AUTHOR);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid->add('name', [
            'label' => t('Name'),
        ]);

        $datagrid->setDefaultOrder('name', OrderingEnum::ASC);
    }

    #[Override]
    public function configureSearch(SearchConfig $search): void
    {
        $search->enableQuickSearch(
            fields: ['name'],
            placeholder: t('Search by name…'),
        );

        $search->addFilter(
            Filter::create('name', t('Name'))
                ->withOperators(Operator::CONTAINS, Operator::NOT_CONTAINS)
                ->apply(static function (QueryBuilder $queryBuilder, FilterRuleCollection $rules): void {
                    foreach ($rules as $rule) {
                        $dqlOperator = $rule->operator === Operator::CONTAINS ? 'LIKE' : 'NOT LIKE';
                        $queryBuilder
                            ->andWhere(sprintf('NORMALIZED(o.name) %s NORMALIZED(:%s)', $dqlOperator, $rule->param()))
                            ->setParameter($rule->param(), $rule->getLikeValue());
                    }
                }),
        );
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(BlogArticleAuthorFormType::class, [
            'blogArticleAuthor' => $entity,
        ]);
    }

    #[Override]
    protected function getEditTemplate(): string
    {
        return '@ShopsysAdministration/content/blogArticleAuthor/edit.html.twig';
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getEditViewData(object $entity): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor $blogArticleAuthor */
        $blogArticleAuthor = $entity;

        return [
            'gridView' => $this->createBlogArticlesGrid($blogArticleAuthor)->createView(),
        ];
    }

    protected function createBlogArticlesGrid(BlogArticleAuthor $blogArticleAuthor): Grid
    {
        $queryBuilder = $this->blogArticleRepository->getBlogArticlesByBlogArticleAuthorQueryBuilder(
            $blogArticleAuthor,
            $this->localization->getCurrentLocaleForTranslatableEntities(),
        );
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'ba.id');

        $grid = $this->gridFactory->create(
            'blogArticleAuthorArticles',
            $dataSource,
            AdminRoleConstant::ROLE_BLOG_ARTICLE_AUTHOR,
        );
        $grid->setAllowedLimits([self::ARTICLES_GRID_DEFAULT_LIMIT, 30, 100]);
        $grid->setDefaultLimit(self::ARTICLES_GRID_DEFAULT_LIMIT);
        $grid->enablePaging();
        $grid->enableScrollToGridOnNavigation();
        $grid->setDefaultOrder('createdAt', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('name', 'bat.name', t('Name'), true);
        $grid->addColumn('createdAt', 'ba.createdAt', t('Date of creation'), true);
        $grid->addEditActionColumn('admin_blogarticle_edit', ['id' => 'ba.id']);

        return $grid;
    }
}
