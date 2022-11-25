<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Symfony\Component\HttpFoundation\Response;

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(int $id): Response
    {
        $article = $this->articleFacade->getVisibleById($id);

        return $this->render('Front/Content/Article/detail.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction(): Response
    {
        $articles = $this->articleFacade->getVisibleArticlesForPlacementOnCurrentDomain(Article::PLACEMENT_TOP_MENU);

        return $this->render('Front/Content/Article/menu.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function footerAction(): Response
    {
        $articles = $this->articleFacade->getVisibleArticlesForPlacementOnCurrentDomain(Article::PLACEMENT_FOOTER);

        return $this->render('Front/Content/Article/menu.html.twig', [
            'articles' => $articles,
        ]);
    }
}
