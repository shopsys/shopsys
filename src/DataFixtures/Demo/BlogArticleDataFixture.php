<?php

declare(strict_types=1);

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
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class BlogArticleDataFixture extends AbstractReferenceFixture
{
    public const PAGES_IN_CATEGORY = 15;

    public const LOCALES = DomainHelper::LOCALES;

    public const FIRST_DEMO_BLOG_ARTICLE = 'first_demo_blog_article';
    public const FIRST_DEMO_BLOG_SUBCATEGORY = 'first_demo_blog_subcategory';
    public const FIRST_DEMO_BLOG_CATEGORY = 'first_demo_blog_category';

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
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
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
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $mainPageBlogCategory = $this->blogCategoryFacade->getById(BlogCategory::BLOG_MAIN_PAGE_CATEGORY_ID);

        $mainPageBlogCategoryData = $this->blogCategoryDataFactory->createFromBlogCategory($mainPageBlogCategory);
        foreach (self::LOCALES as $locale) {
            $mainPageBlogCategoryData->names[$locale] = t('Hlavní stránka blogu - %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
            $mainPageBlogCategoryData->descriptions[$locale] = t('description - Hlavní stránka blogu - %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
        }
        $this->blogCategoryFacade->edit($mainPageBlogCategory->getId(), $mainPageBlogCategoryData);

        $this->addReference(self::FIRST_DEMO_BLOG_CATEGORY, $mainPageBlogCategory);

        //only in main category
        for ($i = 0; $i < self::PAGES_IN_CATEGORY; $i++) {
            $blogArticleData = $this->createArticle([$mainPageBlogCategory]);
            $blogArticle = $this->blogArticleFacade->create($blogArticleData);
            if ($i === 0) {
                $this->addReference(self::FIRST_DEMO_BLOG_ARTICLE, $blogArticle);
            }
        }

        $firstSubcategoryData = $this->createSubcategory($mainPageBlogCategory, 1);
        $firstSubcategory = $this->blogCategoryFacade->create($firstSubcategoryData);
        $this->addReference(self::FIRST_DEMO_BLOG_SUBCATEGORY, $firstSubcategory);

        //in first subcategory
        for ($i = 0; $i < self::PAGES_IN_CATEGORY; $i++) {
            $blogArticleData = $this->createArticle([$mainPageBlogCategory, $firstSubcategory]);
            $this->blogArticleFacade->create($blogArticleData);
        }

        $secondSubcategoryData = $this->createSubcategory($mainPageBlogCategory, 2);
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
     * @param int $subcategoryOrder
     * @return \App\Model\Blog\Category\BlogCategoryData
     */
    private function createSubcategory(BlogCategory $parentCategory, int $subcategoryOrder): BlogCategoryData
    {
        $blogCategoryData = $this->blogCategoryDataFactory->create();
        $blogCategoryData->parent = $parentCategory;

        foreach (self::LOCALES as $locale) {
            if ($subcategoryOrder === 1) {
                $name = t('První podsekce %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
                $description = t('description - První podsekce %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
            } else {
                $name = t('Druhá podsekce %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
                $description = t('description - Druhá podsekce %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
            }
            $blogCategoryData->names[$locale] = $name;
            $blogCategoryData->descriptions[$locale] = $description;
        }

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            if ($subcategoryOrder === 1) {
                $h1 = t('První podsekce %locale% - h1', ['%locale%' => $locale], 'dataFixtures', $locale);
                $title = t('title - První podsekce %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
            } else {
                $h1 = t('Druhá podsekce %locale% - h1', ['%locale%' => $locale], 'dataFixtures', $locale);
                $title = t('title - Druhá podsekce %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
            }
            $blogCategoryData->seoH1s[$domain->getId()] = $h1;
            $blogCategoryData->seoTitles[$domain->getId()] = $title;
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
            $blogArticleData->names[$locale] = t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => $this->articleCounter, '%locale%' => $locale], 'dataFixtures', $locale);
            $blogArticleData->descriptions[$locale] = t('description - Lorem ipsum dolor sit amet, {products=9177759,7700768,9146508} consectetur {products=9177759,9176508} adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.', [], 'dataFixtures', $locale);
            $blogArticleData->perexes[$locale] = t('%locale% perex - lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu.', ['%locale%' => $locale], 'dataFixtures', $locale);
        }

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            $blogArticleData->blogCategoriesByDomainId[$domain->getId()] = $blogCategories;
            $blogArticleData->seoTitles[$domain->getId()] = t('title - Ukázkový článek blogu %counter% %locale%', ['%counter%' => $this->articleCounter, '%locale%' => $locale], 'dataFixtures', $locale);
            $blogArticleData->seoH1s[$domain->getId()] = t('Ukázkový článek blogu %counter% %locale% - H1', ['%counter%' => $this->articleCounter, '%locale%' => $locale], 'dataFixtures', $locale);
        }

        $this->articleCounter++;

        return $blogArticleData;
    }
}
