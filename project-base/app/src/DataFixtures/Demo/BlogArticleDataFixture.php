<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleData;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;

class BlogArticleDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = '7cd16792-7f6c-433c-b038-34ad5f31a215';

    public const int PAGES_IN_CATEGORY = 15;

    public const string FIRST_DEMO_BLOG_ARTICLE = 'first_demo_blog_article';
    public const string FIRST_DEMO_BLOG_SUBCATEGORY = 'first_demo_blog_subcategory';
    public const string FIRST_DEMO_BLOG_CATEGORY = 'first_demo_blog_category';
    public const string DEMO_BLOG_ARTICLE_PREFIX = 'demo_blog_article_';
    public const string SECOND_DEMO_BLOG_SUBCATEGORY = 'second_demo_blog_subcategory';

    private int $articleCounter = 1;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDataFactory $blogArticleDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityFacade $blogVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDataFactory $blogCategoryDataFactory
     */
    public function __construct(
        private readonly BlogArticleFacade $blogArticleFacade,
        private readonly BlogArticleDataFactory $blogArticleDataFactory,
        private readonly BlogCategoryFacade $blogCategoryFacade,
        private readonly BlogVisibilityFacade $blogVisibilityFacade,
        private readonly BlogCategoryDataFactory $blogCategoryDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $mainPageBlogCategory = $this->blogCategoryFacade->getRootBlogCategory();
        $mainPageBlogCategoryData = $this->blogCategoryDataFactory->createFromBlogCategory($mainPageBlogCategory);
        $mainPageBlogCategoryData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Main blog page')->toString();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $domainId = $domainConfig->getId();

            $mainPageBlogCategoryData->names[$locale] = t('Main blog page - %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mainPageBlogCategoryData->descriptions[$locale] = t('description - Main blog page - %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mainPageBlogCategoryData->seoH1s[$domainId] = t('Main blog page - %locale% - H1', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mainPageBlogCategoryData->seoMetaDescriptions[$domainId] = t('Main blog page - %locale% - meta description', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mainPageBlogCategoryData->seoTitles[$domainId] = t('Main blog page - %locale% - Title', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
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

            if ($i === self::PAGES_IN_CATEGORY - 1) {
                $blogArticleData->visibleOnHomepage = false;
            }
            $this->blogArticleFacade->create($blogArticleData);
        }

        $secondSubcategoryData = $this->createSubcategory($mainPageBlogCategory, 2);
        $secondSubcategory = $this->blogCategoryFacade->create($secondSubcategoryData);
        $this->addReference(self::SECOND_DEMO_BLOG_SUBCATEGORY, $secondSubcategory);

        //in second subcategory
        for ($i = 0; $i < self::PAGES_IN_CATEGORY; $i++) {
            $blogArticleData = $this->createArticle([$mainPageBlogCategory, $secondSubcategory]);

            if ($i === self::PAGES_IN_CATEGORY - 1) {
                $blogArticleData->visibleOnHomepage = false;
            }
            $this->blogArticleFacade->create($blogArticleData);
        }

        $this->createBlogArticleForSearchingTest();
        $this->createBlockArticleForProductsTest();
        $this->createBlockArticleWithGrapesJs();

        $this->blogVisibilityFacade->refreshBlogArticlesVisibility();
        $this->blogVisibilityFacade->refreshBlogCategoriesVisibility();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $parentCategory
     * @param int $subcategoryOrder
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData
     */
    private function createSubcategory(BlogCategory $parentCategory, int $subcategoryOrder): BlogCategoryData
    {
        $blogCategoryData = $this->blogCategoryDataFactory->create();
        $blogCategoryData->parent = $parentCategory;

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $domainId = $domainConfig->getId();

            if ($subcategoryOrder === 1) {
                $blogCategoryData->seoH1s[$domainId] = t('First subsection %locale% - h1', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $blogCategoryData->seoTitles[$domainId] = t('title - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $blogCategoryData->names[$locale] = t('First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $blogCategoryData->descriptions[$locale] = t('description - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $blogCategoryData->seoMetaDescriptions[$domainId] = t('description - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            } else {
                $blogCategoryData->seoH1s[$domainId] = t('Second subsection %locale% - h1', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $blogCategoryData->seoTitles[$domainId] = t('title - Second subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $blogCategoryData->names[$locale] = t('Second subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $blogCategoryData->descriptions[$locale] = t('description - Second subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $blogCategoryData->seoMetaDescriptions[$domainId] = t('description - Second subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            }
        }

        if ($subcategoryOrder === 1) {
            $blogCategoryData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'First subsection')->toString();
        } else {
            $blogCategoryData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Second subsection')->toString();
        }

        return $blogCategoryData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $blogCategories
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleData
     */
    private function createArticle(array $blogCategories): BlogArticleData
    {
        $blogArticleData = $this->blogArticleDataFactory->create();

        $dateTime = new DateTime(sprintf('-%s days', $this->articleCounter + 3));
        $blogArticleData->publishDate = $dateTime->setTime(0, 0);
        $blogArticleData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Blog article example ' . $this->articleCounter)->toString();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $blogArticleData->names[$locale] = t('Blog article example %counter% %locale%', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->descriptions[$locale] = str_replace(['    ', PHP_EOL], '', trim(<<<EOT
                <div class="gjs-text-ckeditor">
                    <p>
                        Blog Article Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium, aliquam commodi molestiae atque laudantium in dolorem esse error blanditiis, debitis facere id voluptate. Accusantium mollitia placeat consequatur.
                    </p>
                </div>
                <div class="gjs-text-ckeditor">
                    <h2>Heading H2</h2>
                    <p>
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium, aliquam commodi molestiae atque laudantium in dolorem esse error blanditiis, debitis facere id voluptate. Accusantium mollitia placeat consequatur. Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium, aliquam commodi molestiae atque laudantium in dolorem esse error blanditiis, debitis facere id voluptate. Accusantium mollitia placeat consequatur.
                    </p>
                </div>
                <div class="gjs-products" data-products="9177759,5965879P,9184449,9176544M,7700768"><div class="gjs-product" data-product="9177759"></div><div class="gjs-product" data-product="5965879P"></div><div class="gjs-product" data-product="9184449"></div><div class="gjs-product" data-product="9176544M"></div><div class="gjs-product" data-product="7700768"></div></div>
            EOT));
            $blogArticleData->perexes[$locale] = t('%locale% perex - lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu.', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $domainId = $domainConfig->getId();

            $blogArticleData->blogCategoriesByDomainId[$domainId] = $blogCategories;
            $blogArticleData->seoTitles[$domainId] = t('title - Blog article example %counter% %locale%', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->seoH1s[$domainId] = t('Blog article example %counter% %locale% - H1', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->seoMetaDescriptions[$domainId] = t('Blog article example %counter% %locale% - Meta description', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->articleCounter++;

        return $blogArticleData;
    }

    private function createBlogArticleForSearchingTest(): void
    {
        $blogArticleData = $this->blogArticleDataFactory->create();
        $blogArticleData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Blog article for search testing')->toString();
        $blogArticleData->publishDate = new DateTime('-1 days');

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $blogArticleData->names[$locale] = t('Blog article for search testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->descriptions[$locale] = t(
                '<div class="gjs-text-ckeditor"><p>Blog article text for search testing, the search phrase is &#34;Dina&#34;.</p></div>',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
            $blogArticleData->perexes[$locale] = 'Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum.';
        }

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $domainId = $domainConfig->getId();

            $blogArticleData->blogCategoriesByDomainId[$domainId] = [$this->getReference(self::FIRST_DEMO_BLOG_CATEGORY, BlogCategory::class), $this->getReference(self::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class)];
            $blogArticleData->seoTitles[$domainId] = t('title', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->seoH1s[$domainId] = t('Heading', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $blogArticle = $this->blogArticleFacade->create($blogArticleData);
        $this->addReference(self::DEMO_BLOG_ARTICLE_PREFIX . $blogArticle->getId(), $blogArticle);

        $this->articleCounter++;
    }

    private function createBlockArticleForProductsTest(): void
    {
        $blogArticleData = $this->blogArticleDataFactory->create();
        $blogArticleData->publishDate = new DateTime('-2 days');
        $blogArticleData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Blog article for products testing')->toString();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $blogArticleData->names[$locale] = t('Blog article for products testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->descriptions[$locale] = str_replace(['    ', PHP_EOL], '', trim(<<<EOT
                <div class="gjs-text-ckeditor">
                    <h2>Products</h2>
                    <p>
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium, aliquam commodi molestiae atque laudantium in dolorem esse error blanditiis, debitis facere id voluptate. Accusantium mollitia placeat consequatur. Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium, aliquam commodi molestiae atque laudantium in dolorem esse error blanditiis, debitis facere id voluptate. Accusantium mollitia placeat consequatur.
                    </p>
                </div>
                <div class="gjs-products" data-products="9177759,5965879P,9184449,9176544M,7700768">
                    <div class="gjs-product" data-product="9177759"></div>
                    <div class="gjs-product" data-product="5965879P"></div>
                    <div class="gjs-product" data-product="9184449"></div>
                    <div class="gjs-product" data-product="9176544M"></div>
                    <div class="gjs-product" data-product="7700768"></div>
                </div>
                <div class="gjs-text-ckeditor">
                    <h2>More products</h2>
                    <p>
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium.
                    </p>
                </div>
                <div class="gjs-products" data-products="9177759,5965879P,9184449">
                    <div class="gjs-product" data-product="9177759"></div>
                    <div class="gjs-product" data-product="5965879P"></div>
                    <div class="gjs-product" data-product="9184449"></div>
                </div>
            EOT));
            $blogArticleData->perexes[$locale] = 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium';
        }

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $domainId = $domainConfig->getId();

            $blogArticleData->blogCategoriesByDomainId[$domainId] = [$this->getReference(self::FIRST_DEMO_BLOG_CATEGORY, BlogCategory::class), $this->getReference(self::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class)];
            $blogArticleData->seoTitles[$domainId] = t('Blog article for products testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->seoH1s[$domainId] = t('Blog article for products testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $blogArticle = $this->blogArticleFacade->create($blogArticleData);
        $this->addReference(self::DEMO_BLOG_ARTICLE_PREFIX . $blogArticle->getId(), $blogArticle);

        $this->articleCounter++;
    }

    private function createBlockArticleWithGrapesJs(): void
    {
        $blogArticleData = $this->blogArticleDataFactory->create();
        $blogArticleData->publishDate = new DateTime('-3 days');
        $firstDomainUrl = $this->domainsForDataFixtureProvider->getFirstAllowedDomainConfig()->getUrl();
        $blogArticleData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'GrapesJS page')->toString();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $blogArticleData->names[$locale] = t('GrapesJS page', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->descriptions[$locale] = str_replace(['    ', PHP_EOL], '', trim(<<<EOT
                <div class="gjs-text-ckeditor">
                    <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Fugit magnam illum ex asperiores, non vitae laborum, dignissimos commodi a necessitatibus nobis saepe, soluta sapiente id quisquam quod vel quam hic. Fusce tellu:</p>

                    <ul>
                        <li>Praesent dapibus</li>
                        <ul>
                            <li>Donec vitae arcu</li>
                            <li>Morbi scelerisque luctus velit</li>
                            <ul>
                                <li>Donec vitae arcu</li>
                                <li>Morbi scelerisque luctus velit</li>
                                <li>Donec vitae arcu</li>
                            </ul>
                        </ul>
                        <li>Nam libero tempore, cum soluta nobis est eligendi</li>
                    </ul>

                    <h2>Praesent dapibus</h2>
                    <p>
                        Aliquam ante. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam. Ut enim ad minim veniam, <strong>quis nostrud exercitation</strong> ullamco laboris nisi ut aliquip ex ea commodo consequat. In enim a arcu imperdiet malesuada. Fusce nibh. Integer lacinia. Fusce <strong>aliquam vestibulum</strong> ipsum. Fusce consectetuer risus a nunc. Donec iaculis gravida nulla. Phasellus enim erat, vestibulum vel, aliquam a, <strong>posuere eu</strong>, velit. Morbi imperdiet, mauris ac auctor dictum, nisl ligula egestas nulla, et sollicitudin sem purus in lacus.
                    </p> 

                    <p>
                        <strong>TIP:</strong> <a href="{$firstDomainUrl}" id="ieevs4" tabindex="0">Mauris suscipit, ligula sit amet pharetra semper</a>
                    </p>

                    <h3>Donec vitae arcu</h3>
                    <p>
                        Aenean fermentum risus id tortor. Vivamus ac leo pretium faucibus. Duis risus. Mauris elementum <strong>mauris vitae</strong> tortor. Nulla quis diam. In rutrum. In enim a arcu imperdiet malesuada. Fusce wisi. Integer imperdiet lectus quis justo. Pellentesque ipsum. Aliquam erat volutpat. Etiam <strong>dictum tincidunt</strong> diam.
                    </p>
                    
                    <p>
                        <strong>TIP:</strong> <a href="{$firstDomainUrl}" id="iauj76" tabindex="0">Mauris tincidunt sem sed arcu</a>
                    </p>

                    <h4>Morbi scelerisque luctus velit</h4>
                    <p>
                        Nulla turpis magna, cursus sit amet, suscipit a, interdum id, felis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Phasellus faucibus molestie nisl. Nullam faucibus mi quis velit. Integer imperdiet lectus quis justo. Nulla accumsan, elit sit amet varius semper, nulla mauris mollis quam, tempor suscipit diam nulla vel leo.
                    </p>

                    <p>
                        <strong>TIP:</strong> Mauris vehicula lacinia, quis nostrud exercitation ullamco laboris ...
                    </p>

                    <h5>Nam libero tempore, cum soluta nobis est eligendi</h5>
                    <p>
                        Proin in tellus sit amet nibh dignissim sagittis. Integer in sapien. Curabitur sagittis hendrerit ante. Praesent in mauris eu tortor porttitor accumsan. Aliquam in lorem sit amet leo accumsan lacinia. Nullam rhoncus aliquam metus. Mauris dolor felis, sagittis at, luctus sed, aliquam non, tellus. Aliquam erat volutpat. Duis ante orci, molestie vitae vehicula venenatis, tincidunt ac pede. Duis condimentum augue id magna semper rutrum. Etiam bibendum elit eget erat.
                    </p>

                    <h6>Products</h6>
                    <p>
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium, aliquam commodi molestiae atque laudantium in dolorem esse error blanditiis, debitis facere id voluptate.
                    </p>
                </div>

                <div class="gjs-products" data-products="9177759,9176508,5965879P">
                    <div data-product="9177759" data-product-name="22&quot; Sencor SLE 22F46DM4 HELLO KITTY" class="gjs-product"></div>
                    <div data-product="9176508" data-product-name="32&quot; Philips 32PFL4308" class="gjs-product"></div>
                    <div data-product="5965879P" data-product-name="47&quot; LG 47LA790V (FHD)" class="gjs-product"></div>
                </div>
            EOT));
        }

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $blogArticleData->blogCategoriesByDomainId[$domainId] = [
                $this->getReference(self::FIRST_DEMO_BLOG_CATEGORY, BlogCategory::class),
                $this->getReference(self::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class),
            ];
        }

        $blogArticle = $this->blogArticleFacade->create($blogArticleData);
        $this->addReference(self::DEMO_BLOG_ARTICLE_PREFIX . $blogArticle->getId(), $blogArticle);

        $this->articleCounter++;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
