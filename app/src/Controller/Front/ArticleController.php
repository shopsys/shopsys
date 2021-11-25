<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends FrontBaseController
{
    public const FOOTER_MENU_TWIG_CACHE_KEY = 'footer_menu';

    /**
     * @var \App\Model\Article\ArticleFacade
     */
    private $articleFacade;

    /**
     * @param \App\Model\Article\ArticleFacade $articleFacade
     */
    public function __construct(ArticleFacade $articleFacade)
    {
        $this->articleFacade = $articleFacade;
    }

    /**
     * @param int $id
     */
    public function detailAction($id)
    {
        $article = $this->articleFacade->getVisibleById($id);

        return $this->render('Front/Content/Article/detail.html.twig', [
            'article' => $article,
        ]);
    }

    public function menuAction()
    {
        $articles = $this->articleFacade->getVisibleArticlesForPlacementOnCurrentDomain(Article::PLACEMENT_TOP_MENU);

        return $this->render('Front/Content/Article/menu.html.twig', [
            'articles' => $articles,
            'showBlog' => false,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function footerMenuAction(): Response
    {
        $articlesByPlacements = $this->articleFacade->getVisibleFooterArticlesIndexedByPlacementOnCurrentDomain();

        return $this->render('Front/Content/Article/footerMenu.html.twig', [
            'articlesByPlacements' => $articlesByPlacements,
        ]);
    }
}
