<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

class ArticleController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleFacade
     */
    private $articleFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
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

    public function footerAction($id, $title)
    {
        $articles = $this->articleFacade->getVisibleArticlesForPlacementOnCurrentDomain(
            constant('App\Model\Article\Article::PLACEMENT_FOOTER_' . $id)
        );

        return $this->render('Front/Content/Article/menu.html.twig', [
            'title' => $title,
            'articles' => $articles,
            'showBlog' => true,
        ]);
    }
}
