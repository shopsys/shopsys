<?php

namespace App\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Controller\Admin\ArticleController as BaseArticleController;
use App\Model\Article\Article;

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
