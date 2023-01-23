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
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

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
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @param \App\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \App\Model\Blog\Article\BlogArticleDataFactory $blogArticleDataFactory
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Blog\BlogVisibilityFacade $blogVisibilityFacade
     * @param \App\Model\Blog\Category\BlogCategoryDataFactory $blogCategoryDataFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        BlogArticleFacade $blogArticleFacade,
        BlogArticleDataFactory $blogArticleDataFactory,
        BlogCategoryFacade $blogCategoryFacade,
        Domain $domain,
        BlogVisibilityFacade $blogVisibilityFacade,
        BlogCategoryDataFactory $blogCategoryDataFactory,
        EntityManagerInterface $em
    ) {
        $this->blogArticleFacade = $blogArticleFacade;
        $this->blogArticleDataFactory = $blogArticleDataFactory;
        $this->blogCategoryFacade = $blogCategoryFacade;
        $this->domain = $domain;
        $this->blogVisibilityFacade = $blogVisibilityFacade;
        $this->blogCategoryDataFactory = $blogCategoryDataFactory;
        $this->em = $em;
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
            $mainPageBlogCategoryData->names[$locale] = t('Main blog page - %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
            $mainPageBlogCategoryData->descriptions[$locale] = t('description - Main blog page - %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
            $mainPageBlogCategoryData->seoH1s[$domainId] = t('Main blog page - %locale% - H1', ['%locale%' => $locale], 'dataFixtures', $locale);
            $mainPageBlogCategoryData->seoMetaDescriptions[$domainId] = t('Main blog page - %locale% - meta description', ['%locale%' => $locale], 'dataFixtures', $locale);
            $mainPageBlogCategoryData->seoTitles[$domainId] = t('Main blog page - %locale% - Title', ['%locale%' => $locale], 'dataFixtures', $locale);
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
        $blogCategoryData->uuid = array_pop($this->uuidPool);
        $blogCategoryData->parent = $parentCategory;

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            if ($subcategoryOrder === 1) {
                $h1 = t('First subsection %locale% - h1', ['%locale%' => $locale], 'dataFixtures', $locale);
                $title = t('title - First subsection %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
                $name = t('First subsection %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
                $description = t('description - First subsection %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
            } else {
                $h1 = t('Second subsection %locale% - h1', ['%locale%' => $locale], 'dataFixtures', $locale);
                $title = t('title - Second subsection %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
                $name = t('Second subsection %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
                $description = t('description - Second subsection %locale%', ['%locale%' => $locale], 'dataFixtures', $locale);
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
     * @param \App\Model\Blog\Category\BlogCategory[] $blogCategories
     * @return \App\Model\Blog\Article\BlogArticleData
     */
    private function createArticle(array $blogCategories): BlogArticleData
    {
        $blogArticleData = $this->blogArticleDataFactory->create();

        $blogArticleData->uuid = array_pop($this->uuidPool);

        $blogArticleData->publishDate = new DateTime(sprintf('-3 hours +%s minutes', $this->articleCounter));

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogArticleData->names[$locale] = t('Blog article example %counter% %locale%', ['%counter%' => $this->articleCounter, '%locale%' => $locale], 'dataFixtures', $locale);
            $blogArticleData->descriptions[$locale] = '<div>' . t('description - Lorem ipsum dolor sit amet, {products=9177759,7700768,9146508} consectetur {products=9177759,9176508} adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.', [], 'dataFixtures', $locale) . '</div>';
            $blogArticleData->perexes[$locale] = t('%locale% perex - lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu.', ['%locale%' => $locale], 'dataFixtures', $locale);
        }

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            $blogArticleData->blogCategoriesByDomainId[$domain->getId()] = $blogCategories;
            $blogArticleData->seoTitles[$domain->getId()] = t('title - Blog article example %counter% %locale%', ['%counter%' => $this->articleCounter, '%locale%' => $locale], 'dataFixtures', $locale);
            $blogArticleData->seoH1s[$domain->getId()] = t('Blog article example %counter% %locale% - H1', ['%counter%' => $this->articleCounter, '%locale%' => $locale], 'dataFixtures', $locale);
            $blogArticleData->seoMetaDescriptions[$domain->getId()] = t('Blog article example %counter% %locale% - Meta description', ['%counter%' => $this->articleCounter, '%locale%' => $locale], 'dataFixtures', $locale);
        }

        $this->articleCounter++;

        return $blogArticleData;
    }

    private function createBlogArticleForSearchingTest(): void
    {
        $blogArticleData = $this->blogArticleDataFactory->create();
        $blogArticleData->uuid = array_pop($this->uuidPool);
        $blogArticleData->publishDate = new DateTime('-3 hours');

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogArticleData->names[$locale] = t('Blog article for search testing', [], 'dataFixtures', $locale);
            $blogArticleData->descriptions[$locale] = t('Article text for search testing, the search phrase is "Dina".', [], 'dataFixtures', $locale);
            $blogArticleData->perexes[$locale] = t('perex', ['%locale%' => $locale], 'dataFixtures', $locale);
        }

        foreach ($this->domain->getAll() as $domain) {
            $locale = $domain->getLocale();
            $blogArticleData->blogCategoriesByDomainId[$domain->getId()] = [$this->getReference(self::FIRST_DEMO_BLOG_CATEGORY), $this->getReference(self::FIRST_DEMO_BLOG_SUBCATEGORY)];
            $blogArticleData->seoTitles[$domain->getId()] = t('title', ['%counter%' => $this->articleCounter, '%locale%' => $locale], 'dataFixtures', $locale);
            $blogArticleData->seoH1s[$domain->getId()] = t('Heading', ['%counter%' => $this->articleCounter, '%locale%' => $locale], 'dataFixtures', $locale);
        }

        $this->blogArticleFacade->create($blogArticleData);

        $this->articleCounter++;
    }

    /**
     * {@inheritDoc}
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
                    $id
                )
            )
            ->execute();
    }
}
