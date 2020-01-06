<?php

declare(strict_types = 1);

namespace App\Controller\Front;

use App\Model\Blog\Article\BlogArticleFacade;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogCategoryController extends FrontBaseController
{
    private const BLOG_ARTICLES_PER_PAGE = 12;
    private const PAGE_QUERY_PARAMETER = 'page';

    /**
     * @var \App\Model\Blog\Category\BlogCategoryFacade
     */
    private $blogCategoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Blog\Article\BlogArticleFacade
     */
    private $blogArticleFacade;

    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \App\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        BlogCategoryFacade $blogCategoryFacade,
        BlogArticleFacade $blogArticleFacade,
        Domain $domain
    ) {
        $this->blogCategoryFacade = $blogCategoryFacade;
        $this->domain = $domain;
        $this->blogArticleFacade = $blogArticleFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(Request $request, int $id): Response
    {
        $blogCategory = $this->blogCategoryFacade->getVisibleOnDomainById($this->domain->getId(), $id);

        $requestPage = $request->get(self::PAGE_QUERY_PARAMETER);

        if (!$this->isRequestPageValid($requestPage)) {
            return $this->redirectToRoute('front_blogcategory_detail', ['id' => BlogCategory::BLOG_MAIN_PAGE_CATEGORY_ID]);
        }

        $page = $requestPage === null ? 1 : (int)$requestPage;

        $blogArticlePaginationResult = $this->blogArticleFacade->getPaginationResultForListableInBlogCategory(
            $blogCategory,
            $this->domain->getId(),
            $this->domain->getLocale(),
            $page,
            self::BLOG_ARTICLES_PER_PAGE
        );

        return $this->render('Front/Content/Blog/Category/detail.html.twig', [
            'blogCategory' => $blogCategory,
            'isMainPage' => $blogCategory->isMainPage(),
            'blogArticlePaginationResult' => $blogArticlePaginationResult,
        ]);
    }

    /**
     * @param string|null $page
     * @return bool
     */
    private function isRequestPageValid(?string $page): bool
    {
        return $page === null || (preg_match('@^([2-9]|[1-9][0-9]+)$@', $page));
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $currentBlogCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(BlogCategory $currentBlogCategory): Response
    {
        $childrenBlogCategories = $this->blogCategoryFacade->getAllVisibleChildrenByBlogCategoryAndDomainId(
            $currentBlogCategory,
            $this->domain->getId()
        );

        return $this->render('Front/Content/Blog/Category/list.html.twig', [
            'blogCategories' => $childrenBlogCategories,
        ]);
    }
}
