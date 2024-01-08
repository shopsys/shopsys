<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeoPageGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageRepository $seoPageRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly SeoPageRepository $seoPageRepository,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(int $domainId): Grid
    {
        $queryBuilder = $this->seoPageRepository->getAllQueryBuilder();
        $seoPageDomainRouter = $this->domainRouterFactory->getRouter($domainId);

        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'sp.id',
            function ($row) use ($seoPageDomainRouter) {
                $seoPageId = $row['sp']['id'];
                $seoPageFriendlyUrl = $seoPageDomainRouter->generate('front_page_seo', [
                    'id' => $seoPageId,
                ]);
                $seoPageSlug = SeoPageSlugTransformer::transformFriendlyUrlToSeoPageSlug($seoPageFriendlyUrl);

                if ($seoPageSlug === SeoPage::SEO_PAGE_HOMEPAGE_SLUG) {
                    $targetPageUrl = $seoPageDomainRouter->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
                } else {
                    $targetPageUrl = $seoPageDomainRouter->generate('front_page_seo', [
                        'id' => $seoPageId,
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                }

                $row['seoPageTargetUrl'] = $targetPageUrl;
                $row['seoPageSlug'] = $seoPageSlug;

                return $row;
            },
        );

        $grid = $this->gridFactory->create('seo_page', $dataSource);
        $grid->enablePaging();

        $grid->addColumn('pageName', 'sp.pageName', t('Page name'), true);
        $grid->addColumn('seoPageTargetUrl', 'seoPageTargetUrl', t('Page URL'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_seopage_edit', ['id' => 'sp.id']);
        $grid->addDeleteActionColumn('admin_seopage_deleteconfirm', ['id' => 'sp.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/Seo/Page/listGrid.html.twig');

        return $grid;
    }
}
