<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class FriendlyUrlGridFactory implements GridFactoryInterface
{
    protected QuickSearchFormData $quickSearchFormData;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     */
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly GridFactory $gridFactory,
    ) {
        $this->quickSearchFormData = new QuickSearchFormData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData
     */
    public function getQuickSearchFormData(): QuickSearchFormData
    {
        return $this->quickSearchFormData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchFormData
     */
    public function setQuickSearchFormData(QuickSearchFormData $quickSearchFormData): void
    {
        $this->quickSearchFormData = $quickSearchFormData;
    }

    /**
     * @param int|null $redirectCode
     * @return string
     */
    public function getReadableNameForRedirectCode(?int $redirectCode): string
    {
        if ($redirectCode === null) {
            return '';
        }

        if ($redirectCode === 301) {
            return t('301 (Permanent redirect)');
        }

        return t('302 (Temporary redirect)');
    }

    /**
     * @param string $routeName
     * @return string
     */
    public static function getReadableNameForRouteName(string $routeName): string
    {
        $readableNamesByRouteName = [
            'front_article_detail' => t('Article'),
            'front_blogarticle_detail' => t('Blog article'),
            'front_blogcategory_detail' => t('Blog category'),
            'front_brand_detail' => t('Brand'),
            'front_category_seo' => t('Category with prefilled filters'),
            'front_product_detail' => t('Product'),
            'front_product_list' => t('Category'),
            'front_stores_detail' => t('Store'),
            'front_page_seo' => t('SEO page'),
        ];

        if (array_key_exists($routeName, $readableNamesByRouteName)) {
            return $readableNamesByRouteName[$routeName];
        }

        return $routeName;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create()
    {
        $queryBuilder = $this->friendlyUrlFacade->getNonUsedFriendlyUrlQueryBuilderByDomainIdAndQuickSearch(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $this->getQuickSearchFormData(),
        );

        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'fu.slug',
            function ($row) {
                $row['fu']['routeName'] = $this->getReadableNameForRouteName($row['fu']['routeName']);
                $row['fu']['redirectCode'] = $this->getReadableNameForRedirectCode($row['fu']['redirectCode']);

                return $row;
            },
        );

        $grid = $this->gridFactory->create('notUsedFriendlyUrls', $dataSource);
        $grid->enablePaging();
        $grid->setDefaultOrder('fu.slug');
        $grid->addColumn('slug', 'fu.slug', t('Slug'), true);
        $grid->addColumn('routeName', 'fu.routeName', t('Entity'), true);
        $grid->addColumn('entityId', 'fu.entityId', t('Entity ID'));
        $grid->addColumn('redirectTo', 'fu.redirectTo', t('Redirect target'));
        $grid->addColumn('redirectCode', 'fu.redirectCode', t('Redirect type'), true);
        $grid->addColumn('lastModification', 'fu.lastModification', t('Last modification'), true);
        $grid->addDeleteActionColumn('admin_unused_friendly_url_delete', [
            'domainId' => 'fu.domainId',
            'slug' => 'fu.slug',
        ])
            ->setConfirmMessage(t('Do you really want to remove this friendly URL? Removing friendly URL may have bad impact to SEO performance.'));

        return $grid;
    }
}
