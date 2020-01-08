<?php

declare(strict_types = 1);

namespace App\Controller\Front;

use App\Model\Blog\Article\BlogArticle;
use App\Model\Blog\Article\BlogArticleFacade;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;

class BlogArticleController extends FrontBaseController
{
    public const HOMEPAGE_BLOG_ARTICLES = 2;
    public const MAIN_BLOG_CATEGORY_ID = BlogCategory::BLOG_MAIN_PAGE_CATEGORY_ID;

    /**
     * @var \App\Model\Blog\Article\BlogArticleFacade
     */
    private $blogArticleFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \App\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(BlogArticleFacade $blogArticleFacade, Domain $domain)
    {
        $this->blogArticleFacade = $blogArticleFacade;
        $this->domain = $domain;
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(int $id): Response
    {
        $blogArticle = $this->blogArticleFacade->getVisibleOnDomainById(
            $this->domain->getCurrentDomainConfig(),
            $id
        );

        $domainId = $this->domain->getId();
        $blogCategory = $this->blogArticleFacade->findBlogArticleMainCategoryOnDomain($blogArticle, $domainId);

        return $this->render('Front/Content/Blog/Article/detail.html.twig', [
            'blogArticle' => $blogArticle,
            'domainId' => $this->domain->getId(),
            'blogCategory' => $blogCategory,
        ]);
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticle $blogArticle
     * @param string $spanClass
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mainBlogCategoryForBlogArticleAction(BlogArticle $blogArticle, ?string $spanClass): Response
    {
        return $this->render('Front/Content/Blog/Article/mainBlogCategoryForBlogArticle.html.twig', [
            'blogCategory' => $this->blogArticleFacade->findBlogArticleMainCategoryOnDomain($blogArticle, $this->domain->getId()),
            'spanClass' => $spanClass,
        ]);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homepageArticlesAction(BlogCategoryFacade $blogCategoryFacade): Response
    {
        $mainCategory = $blogCategoryFacade->getVisibleOnDomainById($this->domain->getId(), self::MAIN_BLOG_CATEGORY_ID);

        return $this->render('Front/Content/Blog/Article/homepageArticles.html.twig', [
            'homepageArticles' => $this->blogArticleFacade->getHomepageBlogArticlesByDomainId(
                $this->domain->getId(),
                $this->domain->getLocale(),
                self::HOMEPAGE_BLOG_ARTICLES
            ),
            'rootCategory' => $mainCategory,
        ]);
    }
}
