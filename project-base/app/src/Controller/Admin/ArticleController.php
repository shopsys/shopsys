<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Controller\Admin\ArticleController as BaseArticleController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property \App\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
 */
class ArticleController extends BaseArticleController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory $articleDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     * @param \App\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade $cookiesFacade
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        ArticleFacade $articleFacade,
        ArticleDataFactoryInterface $articleDataFactory,
        GridFactory $gridFactory,
        AdminDomainTabsFacade $adminDomainTabsFacade,
        BreadcrumbOverrider $breadcrumbOverrider,
        ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        LegalConditionsFacade $legalConditionsFacade,
        CookiesFacade $cookiesFacade,
        private readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
        parent::__construct(
            $articleFacade,
            $articleDataFactory,
            $gridFactory,
            $adminDomainTabsFacade,
            $breadcrumbOverrider,
            $confirmDeleteResponseFactory,
            $legalConditionsFacade,
            $cookiesFacade,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function editAction(Request $request, int $id): Response
    {
        $response = parent::editAction($request, $id);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ARTICLES_QUERY_KEY_PART);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function newAction(Request $request): Response
    {
        $response = parent::newAction($request);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ARTICLES_QUERY_KEY_PART);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAction(int $id): Response
    {
        $response = parent::deleteAction($id);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ARTICLES_QUERY_KEY_PART);

        return $response;
    }
}
