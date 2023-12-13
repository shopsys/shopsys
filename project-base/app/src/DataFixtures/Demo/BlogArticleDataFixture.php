<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
    public const PAGES_IN_CATEGORY = 15;

    public const FIRST_DEMO_BLOG_ARTICLE = 'first_demo_blog_article';
    public const FIRST_DEMO_BLOG_SUBCATEGORY = 'first_demo_blog_subcategory';
    public const FIRST_DEMO_BLOG_CATEGORY = 'first_demo_blog_category';

    /**
     * @var string[]
     */
    private array $uuidPool = [
        '7cd16792-7f6c-433c-b038-34ad5f31a215', 'c9fff5b3-82a1-417f-b709-96ed61851f0f', 'e27956d6-7668-4cc6-92c8-f0b5c8f32372',
        '9a361890-683c-4dca-81a1-352f40b15691', '9fcbf9e1-b61c-4991-a220-953d1120f33c', 'bc08accd-7334-48e9-871f-036878dfee19',
        '649be426-b4ce-4cf0-a6da-e0bd2cccbc59', '12285722-d933-4796-a83b-c88b844f393a', '2856b769-92f6-48e2-831e-ea4cac631f88',
        'fb9f8e37-8807-45d3-a249-0a150f925a26', '3e4dd917-0fea-4cb7-8869-785e74178bb1', '653b9044-002b-485a-b537-e975a5369bc1',
        'b283f63a-57a6-4444-8981-8341ec5b9326', 'cfd629f5-de34-41fa-b414-fa6a347d1df3', 'f1b84790-938e-4716-b824-cbb545be7533',
        'ad516d3c-456c-4e11-a462-e0d9f052b8a3', 'e7eb1163-95a8-400d-b2a4-d0348b87ac23', 'f917454e-bafd-4312-9de0-f35904e7e9b0',
        '05e390a9-4ff0-43f3-9282-8be8c67ff234', 'c6e8e941-b568-4aaf-b74c-de4053f22d8f', '0796a3a6-5226-4083-a4ad-c9b92077fd82',
        '1731e79a-788e-4151-a2b8-3a475c05af5c', '3ec93db3-fdef-4c75-86e6-d2ac9c02c20c', 'c3758696-d814-4402-8687-180c0a8eb0ed',
        'edbec10c-3372-46cd-97b4-d1b35920cd21', '509e682f-8088-47f3-905a-dffaa2700c30', '14b33e96-b7b0-41ee-adf9-5d60423b1e15',
        '826fe5ea-3113-4b25-9c1e-78b16a3d3bcf', '1ca426c3-528e-4686-92fe-8708cdd2387c', 'badaeb17-5c21-49a4-b9f8-d936641b0712',
        '98828098-a901-4e49-922a-6fe151b82feb', '67741a23-42d6-4c3b-b1f9-ee585613c8f2', '5fa60c37-f1af-4275-9419-bf6015874a7d',
        '62c66c64-0f4f-48e6-a76e-f8b05d8d3d28', '69bd0825-d687-43fa-8cf5-2a231639f291', '5f93c6ca-8a23-4e25-8897-6a23b37d5a04',
        'f1dc28d4-6d8d-44d1-8beb-64b407ca1c46', '184cf1cb-9885-4338-b3c8-91634a621687', '3eb20f86-e59f-412c-a537-3254b708d5f9',
        '3abe40ff-f1d0-4b06-9ffd-a09da457cee4', '694cff26-f479-46b8-9ddf-d7647468f722', '38e46427-9aac-4148-b152-6ed92f75a572',
        '1f4fba66-bb78-40cc-a9ef-1d589ac384ea', '7b384079-c744-4816-bea4-4484ca654756', 'fba0c8d4-cbcd-4c4a-96d9-f8759c0dbecc',
        '41b8d6bf-1fe0-462d-91ba-e17adcdd3944', '654be677-983f-4f33-a8f6-996bf0b2a7c2', '064d88ef-a017-440f-8cab-7641aaab256f',
    ];

    private int $articleCounter = 1;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDataFactory $blogArticleDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityFacade $blogVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDataFactory $blogCategoryDataFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        private BlogArticleFacade $blogArticleFacade,
        private BlogArticleDataFactory $blogArticleDataFactory,
        private BlogCategoryFacade $blogCategoryFacade,
        private Domain $domain,
        private BlogVisibilityFacade $blogVisibilityFacade,
        private BlogCategoryDataFactory $blogCategoryDataFactory,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $mainPageBlogCategory = $this->blogCategoryFacade->getById(BlogCategory::BLOG_MAIN_PAGE_CATEGORY_ID);

        $this->updateBlogCategoryUuid($mainPageBlogCategory->getId(), '5247c908-b258-43ee-b184-015ee77df608');
        $this->updateBlogCategoryUuid($mainPageBlogCategory->getParent()->getId(), '77f0ef08-871e-4099-855f-07650eaaf64d');
        $mainPageBlogCategoryData = $this->blogCategoryDataFactory->createFromBlogCategory($mainPageBlogCategory);

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            $domainId = $domain->getId();
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
        $blogCategoryData->uuid = array_pop($this->uuidPool);
        $blogCategoryData->parent = $parentCategory;

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();

            if ($subcategoryOrder === 1) {
                $h1 = t('First subsection %locale% - h1', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $title = t('title - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $name = t('First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $description = t('description - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            } else {
                $h1 = t('Second subsection %locale% - h1', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $title = t('title - Second subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $name = t('Second subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $description = t('description - Second subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            }
            $blogCategoryData->seoH1s[$domain->getId()] = $h1;
            $blogCategoryData->seoTitles[$domain->getId()] = $title;
            $blogCategoryData->seoMetaDescriptions[$domain->getId()] = $description;
            $blogCategoryData->names[$locale] = $name;
            $blogCategoryData->descriptions[$locale] = $description;
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

        $blogArticleData->uuid = array_pop($this->uuidPool);

        $blogArticleData->publishDate = new DateTime(sprintf('-%s days', $this->articleCounter + 3));

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogArticleData->names[$locale] = t('Blog article example %counter% %locale%', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->descriptions[$locale] = t(
                '<div class="gjs-text-ckeditor">
                    description - Lorem ipsum dolor sit amet,
                </div>
                %productsFirstRow%
                <div class="gjs-text-ckeditor">
                    consectetur
                </div>
                %productsSecondRow%
                <div class="gjs-text-ckeditor">adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu,
                    laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta.
                    Vivamus pulvinar sem non auctor dictum.
                    Morbi eleifend semper enim, eu faucibus tortor posuere vitae.
                    Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt.
                    Aenean sed velit massa. Nullam interdum eget est ut convallis.
                    Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.
                    \nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu.
                    Vivamus convallis quam vulputate faucibus facilisis.
                    Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a.
                    Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam.
                    In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere.
                    Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.
                </div>',
                [
                    '%productsFirstRow%' => '<div class="gjs-products" data-products="9177759,7700768,9146508"><div class="gjs-product" data-product="9177759"></div><div class="gjs-product" data-product="7700768"></div><div class="gjs-product" data-product="9146508"></div></div>',
                    '%productsSecondRow%' => '<div class="gjs-products" data-products="9177759,9176508"><div class="gjs-product" data-product="9177759"></div><div class="gjs-product" data-product="9176508"></div></div>',
                ],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
            $blogArticleData->perexes[$locale] = t('%locale% perex - lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu.', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            $blogArticleData->blogCategoriesByDomainId[$domain->getId()] = $blogCategories;
            $blogArticleData->seoTitles[$domain->getId()] = t('title - Blog article example %counter% %locale%', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->seoH1s[$domain->getId()] = t('Blog article example %counter% %locale% - H1', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->seoMetaDescriptions[$domain->getId()] = t('Blog article example %counter% %locale% - Meta description', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->articleCounter++;

        return $blogArticleData;
    }

    private function createBlogArticleForSearchingTest(): void
    {
        $blogArticleData = $this->blogArticleDataFactory->create();
        $blogArticleData->uuid = array_pop($this->uuidPool);
        $blogArticleData->publishDate = new DateTime('-1 days');

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogArticleData->names[$locale] = t('Blog article for search testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->descriptions[$locale] = t('Article text for search testing, the search phrase is &#34;Dina&#34;.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->perexes[$locale] = t('perex', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            $blogArticleData->blogCategoriesByDomainId[$domain->getId()] = [$this->getReference(self::FIRST_DEMO_BLOG_CATEGORY), $this->getReference(self::FIRST_DEMO_BLOG_SUBCATEGORY)];
            $blogArticleData->seoTitles[$domain->getId()] = t('title', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->seoH1s[$domain->getId()] = t('Heading', ['%counter%' => $this->articleCounter, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->blogArticleFacade->create($blogArticleData);

        $this->articleCounter++;
    }

    private function createBlockArticleForProductsTest(): void
    {
        $blogArticleData = $this->blogArticleDataFactory->create();
        $blogArticleData->publishDate = new DateTime('-2 days');

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogArticleData->names[$locale] = t('Blog article for products testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->descriptions[$locale] = str_replace(['    ', PHP_EOL], '', trim(<<<EOT
    <div class="gjs-text-ckeditor"><h2>Produkty 1</h2></div>
    <div class="gjs-products" data-products="9177759,9176508,5960453,9772572,8981018">
        <div class="gjs-product" data-product="9177759"></div>
        <div class="gjs-product" data-product="9176508"></div>
        <div class="gjs-product" data-product="5960453"></div>
        <div class="gjs-product" data-product="9772572"></div>
        <div class="gjs-product" data-product="8981018"></div>
    </div>
    <div class="gjs-text-ckeditor"><h2>Produkty 2</h2></div>
    <div class="gjs-products" data-products="9177759,9176508,5960453">
        <div class="gjs-product" data-product="9177759"></div>
        <div class="gjs-product" data-product="9176508"></div>
        <div class="gjs-product" data-product="5960453"></div>
    </div>
EOT));

            $blogArticleData->perexes[$locale] = t('Blog article for products testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            $blogArticleData->blogCategoriesByDomainId[$domain->getId()] = [$this->getReference(self::FIRST_DEMO_BLOG_CATEGORY), $this->getReference(self::FIRST_DEMO_BLOG_SUBCATEGORY)];
            $blogArticleData->seoTitles[$domain->getId()] = t('Blog article for products testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->seoH1s[$domain->getId()] = t('Blog article for products testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->blogArticleFacade->create($blogArticleData);

        $this->articleCounter++;
    }

    private function createBlockArticleWithGrapesJs(): void
    {
        $blogArticleData = $this->blogArticleDataFactory->create();
        $blogArticleData->publishDate = new DateTime('-3 days');
        $firstDomainUrl = $this->domain->getDomainConfigById(1)->getUrl();

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogArticleData->names[$locale] = t('GrapesJS page', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->descriptions[$locale] = str_replace(['    ', PHP_EOL], '', trim(<<<EOT
<style>#i3wiwe{padding-top:15px;padding-bottom:15px;}#i47xqe{color:black;}#ijhc4t{color:black;width:533px;height:324px;}#ie4jei{color:black;width:1157px;}</style>
<div class="gjs-text-ckeditor"><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Pellentesque arcu. Fusce tellu:</p>

<ul>
	<li>Praesent dapibus</li>
	<li>Donec vitae arcu</li>
	<li>Morbi scelerisque luctus velit</li>
	<li>Nam libero tempore, cum soluta nobis est eligendi</li>
</ul>

<h2>Praesent dapibus</h2>
Aliquam ante. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam. Ut enim ad minim veniam, <strong>quis nostrud exercitation</strong> ullamco laboris nisi ut aliquip ex ea commodo consequat. In enim a arcu imperdiet malesuada. Fusce nibh. Integer lacinia. Fusce <strong>aliquam vestibulum</strong> ipsum. Fusce consectetuer risus a nunc. Donec iaculis gravida nulla. Phasellus enim erat, vestibulum vel, aliquam a, <strong>posuere eu</strong>, velit. Morbi imperdiet, mauris ac auctor dictum, nisl ligula egestas nulla, et sollicitudin sem purus in lacus.<br />
<br />
<strong>TIP:</strong><a href="{$firstDomainUrl}" id="ieevs4">Mauris suscipit, ligula sit amet pharetra semper</a>

<h2>Donec vitae arcu</h2>
Aenean fermentum risus id tortor. Vivamus ac leo pretium faucibus. Duis risus. Mauris elementum <strong>mauris vitae</strong> tortor. Nulla quis diam. In rutrum. In enim a arcu imperdiet malesuada. Fusce wisi. Integer imperdiet lectus quis justo. Pellentesque ipsum. Aliquam erat volutpat. Etiam <strong>dictum tincidunt</strong> diam.<br />
<br />
<strong>TIP:</strong><a href="{$firstDomainUrl}" id="iauj76">Mauris tincidunt sem sed arcu</a>

<h2>Morbi scelerisque luctus velit</h2>
Nulla turpis magna, cursus sit amet, suscipit a, interdum id, felis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Phasellus faucibus molestie nisl. Nullam faucibus mi quis velit. Integer imperdiet lectus quis justo. Nulla accumsan, elit sit amet varius semper, nulla mauris mollis quam, tempor suscipit diam nulla vel leo.<br />
<br />
<strong>TIP:</strong>Mauris vehicula lacinia, quis nostrud exercitation ullamco laboris ...<br />
<br />

<h2>Nam libero tempore, cum soluta nobis est eligendi</h2>
Proin in tellus sit amet nibh dignissim sagittis. Integer in sapien. Curabitur sagittis hendrerit ante. Praesent in mauris eu tortor porttitor accumsan. Aliquam in lorem sit amet leo accumsan lacinia. Nullam rhoncus aliquam metus. Mauris dolor felis, sagittis at, luctus sed, aliquam non, tellus. Aliquam erat volutpat. Duis ante orci, molestie vitae vehicula venenatis, tincidunt ac pede. Duis condimentum augue id magna semper rutrum. Etiam bibendum elit eget erat.<br />
</div>

<div class="gjs-products" data-products="9177759,9176508,5965879P">
<div data-product="9177759" data-product-name="22&quot; Sencor SLE 22F46DM4 HELLO KITTY" class="gjs-product"></div>
<div data-product="9176508" data-product-name="32&quot; Philips 32PFL4308" class="gjs-product"></div>
<div data-product="5965879P" data-product-name="47&quot; LG 47LA790V (FHD)" class="gjs-product"></div>
</div>
EOT));
        }

        foreach ($this->domain->getAll() as $domain) {
            $blogArticleData->blogCategoriesByDomainId[$domain->getId()] = [$this->getReference(self::FIRST_DEMO_BLOG_CATEGORY), $this->getReference(self::FIRST_DEMO_BLOG_SUBCATEGORY)];
        }

        $this->blogArticleFacade->create($blogArticleData);

        $this->articleCounter++;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }

    /**
     * @param int $id
     * @param string $uuid
     */
    private function updateBlogCategoryUuid(int $id, string $uuid): void
    {
        $this->em
            ->createQuery(
                sprintf(
                    'UPDATE %s bc SET bc.uuid = \'%s\' WHERE bc.id = %d',
                    BlogCategory::class,
                    $uuid,
                    $id,
                ),
            )
            ->execute();
    }
}
