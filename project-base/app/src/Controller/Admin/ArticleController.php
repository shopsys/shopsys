<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Article\Article;
use Shopsys\FrameworkBundle\Controller\Admin\ArticleController as BaseArticleController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Article\ArticleDataFactory $articleDataFactory
 * @method __construct(\App\Model\Article\ArticleFacade $articleFacade, \App\Model\Article\ArticleDataFactory $articleDataFactory, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory, \App\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade, \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade $cookiesFacade)
 * @property \App\Model\Article\ArticleFacade $articleFacade
 * @property \App\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
 */
class ArticleController extends BaseArticleController
{
    /**
     * @Route("/article/list/")
     */
    public function listAction()
    {
        $gridTop = $this->getGrid(Article::PLACEMENT_TOP_MENU);
        $gridFooter1 = $this->getGrid(Article::PLACEMENT_FOOTER_1);
        $gridFooter2 = $this->getGrid(Article::PLACEMENT_FOOTER_2);
        $gridFooter3 = $this->getGrid(Article::PLACEMENT_FOOTER_3);
        $gridFooter4 = $this->getGrid(Article::PLACEMENT_FOOTER_4);
        $gridNone = $this->getGrid(Article::PLACEMENT_NONE);
        $articlesCountOnSelectedDomain = $this->articleFacade->getAllArticlesCountByDomainId($this->adminDomainTabsFacade->getSelectedDomainId());

        return $this->render('@ShopsysFramework/Admin/Content/Article/list.html.twig', [
            'gridViewTop' => $gridTop->createView(),
            'gridViewFooter1' => $gridFooter1->createView(),
            'gridViewFooter2' => $gridFooter2->createView(),
            'gridViewFooter3' => $gridFooter3->createView(),
            'gridViewFooter4' => $gridFooter4->createView(),
            'gridViewNone' => $gridNone->createView(),
            'articlesCountOnSelectedDomain' => $articlesCountOnSelectedDomain,
        ]);
    }
}
