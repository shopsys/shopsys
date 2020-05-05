<?php

declare(strict_types = 1);

namespace App\DataFixtures\Demo;

use App\Model\Blog\Article\BlogArticleData;
use App\Model\Blog\Article\BlogArticleDataFactory;
use App\Model\Blog\Article\BlogArticleFacade;
use App\Model\Blog\BlogVisibilityFacade;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Blog\Category\BlogCategoryData;
use App\Model\Blog\Category\BlogCategoryDataFactory;
use App\Model\Blog\Category\BlogCategoryFacade;
use App\Model\Domain\DomainHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class BlogArticleDataFixture extends AbstractReferenceFixture
{
    public const PAGES_IN_CATEGORY = 15;

    public const LOCALES = DomainHelper::LOCALES;

    /**
     * @var \App\Model\Blog\Article\BlogArticleFacade
     */
    private $blogArticleFacade;

    /**
     * @var \App\Model\Blog\Article\BlogArticleDataFactory
     */
    private $blogArticleDataFactory;

    /**
     * @var \App\Model\Blog\Category\BlogCategoryFacade
     */
    private $blogCategoryFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Blog\BlogVisibilityFacade
     */
    private $blogVisibilityFacade;

    /**
     * @var \App\Model\Blog\Category\BlogCategoryDataFactory
     */
    private $blogCategoryDataFactory;

    /**
     * @var int
     */
    private $articleCounter = 1;

    /**
     * @param \App\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \App\Model\Blog\Article\BlogArticleDataFactory $blogArticleDataFactory
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Blog\BlogVisibilityFacade $blogVisibilityFacade
     * @param \App\Model\Blog\Category\BlogCategoryDataFactory $blogCategoryDataFactory
     */
    public function __construct(
        BlogArticleFacade $blogArticleFacade,
        BlogArticleDataFactory $blogArticleDataFactory,
        BlogCategoryFacade $blogCategoryFacade,
        Domain $domain,
        BlogVisibilityFacade $blogVisibilityFacade,
        BlogCategoryDataFactory $blogCategoryDataFactory
    ) {
        $this->blogArticleFacade = $blogArticleFacade;
        $this->blogArticleDataFactory = $blogArticleDataFactory;
        $this->blogCategoryFacade = $blogCategoryFacade;
        $this->domain = $domain;
        $this->blogVisibilityFacade = $blogVisibilityFacade;
        $this->blogCategoryDataFactory = $blogCategoryDataFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $mainPageBlogCategory = $this->blogCategoryFacade->getById(BlogCategory::BLOG_MAIN_PAGE_CATEGORY_ID);

        $mainPageBlogCategoryData = $this->blogCategoryDataFactory->createFromBlogCategory($mainPageBlogCategory);
        foreach (self::LOCALES as $locale) {
            $name = 'Hlavní stránka blogu';
            $mainPageBlogCategoryData->names[$locale] = $name . ' - ' . $locale;
            $mainPageBlogCategoryData->descriptions[$locale] = 'description - ' . $name . ' - ' . $locale;
        }
        $this->blogCategoryFacade->edit($mainPageBlogCategory->getId(), $mainPageBlogCategoryData);

        //only in main category
        for ($i = 0; $i < self::PAGES_IN_CATEGORY; $i++) {
            $blogArticleData = $this->createArticle([$mainPageBlogCategory]);
            $this->blogArticleFacade->create($blogArticleData);
        }

        $firstSubcategoryData = $this->createSubcategory($mainPageBlogCategory, 'První podsekce');
        $firstSubcategory = $this->blogCategoryFacade->create($firstSubcategoryData);

        //in first subcategory
        for ($i = 0; $i < self::PAGES_IN_CATEGORY; $i++) {
            $blogArticleData = $this->createArticle([$mainPageBlogCategory, $firstSubcategory]);
            $this->blogArticleFacade->create($blogArticleData);
        }

        $secondSubcategoryData = $this->createSubcategory($mainPageBlogCategory, 'Druhá podsekce');
        $secondSubcategory = $this->blogCategoryFacade->create($secondSubcategoryData);

        //in second subcategory
        for ($i = 0; $i < self::PAGES_IN_CATEGORY; $i++) {
            $blogArticleData = $this->createArticle([$mainPageBlogCategory, $secondSubcategory]);
            $this->blogArticleFacade->create($blogArticleData);
        }

        $this->blogVisibilityFacade->refreshBlogArticlesVisibility();
        $this->blogVisibilityFacade->refreshBlogCategoriesVisibility();
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $parentCategory
     * @param string $name
     * @return \App\Model\Blog\Category\BlogCategoryData
     */
    private function createSubcategory(BlogCategory $parentCategory, string $name): BlogCategoryData
    {
        $blogCategoryData = $this->blogCategoryDataFactory->create();
        $blogCategoryData->parent = $parentCategory;

        foreach (self::LOCALES as $locale) {
            $blogCategoryData->names[$locale] = $name . ' ' . $locale;
            $blogCategoryData->descriptions[$locale] = 'description - ' . $name . ' - ' . $locale;
        }

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            $blogCategoryData->seoH1s[$domain->getId()] = $name . ' ' . $locale . ' - h1';
            $blogCategoryData->seoTitles[$domain->getId()] = 'title - ' . $name . ' ' . $locale;
        }

        return $blogCategoryData;
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory[] $blogCategories
     * @return \App\Model\Blog\Article\BlogArticleData
     */
    private function createArticle(array $blogCategories): BlogArticleData
    {
        $blogArticleData = $this->blogArticleDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogArticleData->names[$locale] = 'Ukázkový článek blogu' . $this->articleCounter . ' ' . $locale;
            $blogArticleData->descriptions[$locale] = 'description - Lorem ipsum dolor sit amet, {products=9177759,7700768,9146508} consectetur {products=9177759,9176508} adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.';
            $blogArticleData->perexes[$locale] = $locale . ' perex - lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu.';
        }

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            $blogArticleData->blogCategoriesByDomainId[$domain->getId()] = $blogCategories;
            $blogArticleData->seoTitles[$domain->getId()] = 'title - ' . $blogArticleData->names[$locale];
            $blogArticleData->seoH1s[$domain->getId()] = $blogArticleData->names[$locale] . ' - H1';
        }

        $this->articleCounter++;

        return $blogArticleData;
    }
}
