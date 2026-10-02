<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\DataFixtures\Demo\DemoDataFactory\BlogArticleContentFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleData;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleStatusEnum;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor;
use Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Symfony\Component\Clock\DatePoint;

class BlogArticleDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = '7cd16792-7f6c-433c-b038-34ad5f31a215';

    private const int ARTICLES_IN_ADDITIONAL_SUBCATEGORY = 2;
    private const int ARTICLE_TITLE_TOPICS_COUNT = 5;

    public const int PAGES_IN_CATEGORY = 7;

    public const string FIRST_DEMO_BLOG_ARTICLE = 'first_demo_blog_article';
    public const string FIRST_DEMO_BLOG_SUBCATEGORY = 'first_demo_blog_subcategory';
    public const string FIRST_DEMO_BLOG_CATEGORY = 'first_demo_blog_category';
    public const string BLOG_CATEGORY_SCREEN_TECHNOLOGIES = 'blog_category_screen_technologies';
    public const string BLOG_CATEGORY_TELEVISIONS = 'blog_category_televisions';
    public const string SECOND_DEMO_BLOG_SUBCATEGORY = 'second_demo_blog_subcategory';
    public const string BLOG_ARTICLE_DRAFT = 'blog_article_draft';
    public const string BLOG_ARTICLE_PREVIEW = 'blog_article_preview';
    public const string BLOG_ARTICLE_PUBLISHED_FUTURE = 'blog_article_published_future';
    public const string BLOG_ARTICLE_WITH_AUTHOR = 'blog_article_with_author';
    public const string BLOG_ARTICLE_WITHOUT_AUTHOR = 'blog_article_without_author';

    private int $articleCounter = 1;

    private int $blogArticleAuthorRotation = 0;

    public function __construct(
        private readonly BlogArticleFacade $blogArticleFacade,
        private readonly BlogArticleDataFactory $blogArticleDataFactory,
        private readonly BlogCategoryFacade $blogCategoryFacade,
        private readonly BlogVisibilityFacade $blogVisibilityFacade,
        private readonly BlogCategoryDataFactory $blogCategoryDataFactory,
        private readonly BlogArticleContentFactory $blogArticleContentFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $mainPageBlogCategory = $this->blogCategoryFacade->getRootBlogCategory();
        $mainPageBlogCategoryData = $this->blogCategoryDataFactory->createFromBlogCategory($mainPageBlogCategory);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $domainId = $domainConfig->getId();

            $mainPageBlogCategoryData->names[$locale] = t('Main blog page - %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mainPageBlogCategoryData->descriptions[$locale] = t('description - Main blog page - %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mainPageBlogCategoryData->seo[$domainId]->h1 = t('Main blog page - %locale% - H1', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mainPageBlogCategoryData->seo[$domainId]->metaDescription = t('Main blog page - %locale% - meta description', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mainPageBlogCategoryData->seo[$domainId]->title = t('Main blog page - %locale% - Title', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->blogCategoryFacade->edit($mainPageBlogCategory->getId(), $mainPageBlogCategoryData);

        $this->addReference(self::FIRST_DEMO_BLOG_CATEGORY, $mainPageBlogCategory);

        $firstSubcategoryData = $this->createSubcategory($mainPageBlogCategory, 1);
        $firstSubcategory = $this->blogCategoryFacade->create($firstSubcategoryData);
        $this->addReference(self::FIRST_DEMO_BLOG_SUBCATEGORY, $firstSubcategory);

        $televisionsCategory = $this->blogCategoryFacade->create($this->createSubcategory($firstSubcategory, 6));
        $this->addReference(self::BLOG_CATEGORY_TELEVISIONS, $televisionsCategory);
        $audioCategory = $this->blogCategoryFacade->create($this->createSubcategory($firstSubcategory, 7));
        $screenTechnologiesCategory = $this->blogCategoryFacade->create($this->createSubcategory($televisionsCategory, 8));
        $this->addReference(self::BLOG_CATEGORY_SCREEN_TECHNOLOGIES, $screenTechnologiesCategory);

        $secondSubcategoryData = $this->createSubcategory($mainPageBlogCategory, 2);
        $secondSubcategory = $this->blogCategoryFacade->create($secondSubcategoryData);
        $this->addReference(self::SECOND_DEMO_BLOG_SUBCATEGORY, $secondSubcategory);

        $additionalCategoriesByArticleGroup = $this->createAdditionalSubcategories($mainPageBlogCategory);

        $mainBlogArticle = $this->createMainBlogArticle(
            $mainPageBlogCategory,
            $firstSubcategory,
        );
        $this->addReference(self::FIRST_DEMO_BLOG_ARTICLE, $mainBlogArticle);
        $this->addReference(self::BLOG_ARTICLE_WITH_AUTHOR, $mainBlogArticle);

        $this->createGeneralArticles(
            $mainPageBlogCategory,
            $secondSubcategory,
            $additionalCategoriesByArticleGroup,
        );
        $this->createBuyingGuideArticles(
            $mainPageBlogCategory,
            $firstSubcategory,
            $televisionsCategory,
            $audioCategory,
            $screenTechnologiesCategory,
        );
        $this->createInspirationArticles($mainPageBlogCategory, $secondSubcategory);

        $this->createArticlesInAdditionalSubcategories(
            $additionalCategoriesByArticleGroup,
            $mainPageBlogCategory,
            $screenTechnologiesCategory,
        );

        $this->blogVisibilityFacade->refreshBlogArticlesVisibility();
        $this->blogVisibilityFacade->refreshBlogCategoriesVisibility();
    }

    /**
     * @param array<string, \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]> $additionalCategoriesByArticleGroup
     */
    private function createGeneralArticles(
        BlogCategory $mainPageBlogCategory,
        BlogCategory $secondSubcategory,
        array $additionalCategoriesByArticleGroup,
    ): void {
        for ($i = 0; $i < self::PAGES_IN_CATEGORY; $i++) {
            $blogArticleData = $this->createArticle(
                $this->getGeneralArticleCategories(
                    $i,
                    $mainPageBlogCategory,
                    $secondSubcategory,
                    $additionalCategoriesByArticleGroup,
                ),
                BlogArticleContentFactory::ARTICLE_GROUP_GENERAL,
                $i,
            );
            $this->applyStatusDiversity($blogArticleData, $i);

            if ($i === 1) {
                $blogArticleData->blogArticleAuthor = null;
            }

            $blogArticle = $this->blogArticleFacade->create($blogArticleData);

            if ($i === 1) {
                $this->addReference(self::BLOG_ARTICLE_WITHOUT_AUTHOR, $blogArticle);
            } elseif ($i === self::PAGES_IN_CATEGORY - 1) {
                $this->addReference(self::BLOG_ARTICLE_DRAFT, $blogArticle);
            } elseif ($i === self::PAGES_IN_CATEGORY - 2) {
                $this->addReference(self::BLOG_ARTICLE_PREVIEW, $blogArticle);
            } elseif ($i === self::PAGES_IN_CATEGORY - 3) {
                $this->addReference(self::BLOG_ARTICLE_PUBLISHED_FUTURE, $blogArticle);
            }
        }
    }

    private function createBuyingGuideArticles(
        BlogCategory $mainPageBlogCategory,
        BlogCategory $firstSubcategory,
        BlogCategory $televisionsCategory,
        BlogCategory $audioCategory,
        BlogCategory $screenTechnologiesCategory,
    ): void {
        for ($i = 0; $i < self::PAGES_IN_CATEGORY; $i++) {
            $blogArticleData = $this->createArticle(
                $this->getBuyingGuideArticleCategories(
                    $i,
                    $mainPageBlogCategory,
                    $firstSubcategory,
                    $televisionsCategory,
                    $audioCategory,
                    $screenTechnologiesCategory,
                ),
                BlogArticleContentFactory::ARTICLE_GROUP_BUYING_GUIDE,
                $i,
            );
            $this->applyStatusDiversity($blogArticleData, $i);

            if ($i === self::PAGES_IN_CATEGORY - 1) {
                $blogArticleData->visibleOnHomepage = false;
            }
            $this->blogArticleFacade->create($blogArticleData);
        }
    }

    private function createInspirationArticles(
        BlogCategory $mainPageBlogCategory,
        BlogCategory $secondSubcategory,
    ): void {
        for ($i = 0; $i < self::PAGES_IN_CATEGORY; $i++) {
            $blogArticleData = $this->createArticle(
                [$mainPageBlogCategory, $secondSubcategory],
                BlogArticleContentFactory::ARTICLE_GROUP_INSPIRATION,
                $i,
            );
            $this->applyStatusDiversity($blogArticleData, $i);

            if ($i === self::PAGES_IN_CATEGORY - 1) {
                $blogArticleData->visibleOnHomepage = false;
            }
            $this->blogArticleFacade->create($blogArticleData);
        }
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]>
     */
    private function createAdditionalSubcategories(BlogCategory $mainPageBlogCategory): array
    {
        $categoryDataByOrder = [
            3 => ['articleGroup' => BlogArticleContentFactory::ARTICLE_GROUP_PRODUCT_NEWS],
            4 => ['articleGroup' => BlogArticleContentFactory::ARTICLE_GROUP_CARE, 'childOrder' => 9],
            5 => ['articleGroup' => BlogArticleContentFactory::ARTICLE_GROUP_TECHNOLOGY],
        ];
        $additionalCategoriesByArticleGroup = [];

        foreach ($categoryDataByOrder as $subcategoryOrder => $categoryData) {
            $subcategoryData = $this->createSubcategory($mainPageBlogCategory, $subcategoryOrder);
            $subcategory = $this->blogCategoryFacade->create($subcategoryData);
            $articleCategories = [$subcategory];

            if (isset($categoryData['childOrder'])) {
                $articleCategories[] = $this->blogCategoryFacade->create(
                    $this->createSubcategory($subcategory, $categoryData['childOrder']),
                );
            }

            $additionalCategoriesByArticleGroup[$categoryData['articleGroup']] = $articleCategories;
        }

        return $additionalCategoriesByArticleGroup;
    }

    /**
     * @param array<string, \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]> $additionalCategoriesByArticleGroup
     */
    private function createArticlesInAdditionalSubcategories(
        array $additionalCategoriesByArticleGroup,
        BlogCategory $mainPageBlogCategory,
        BlogCategory $screenTechnologiesCategory,
    ): void {
        foreach ($additionalCategoriesByArticleGroup as $articleGroup => $articleCategories) {
            for ($i = 0; $i < self::ARTICLES_IN_ADDITIONAL_SUBCATEGORY; $i++) {
                $assignedCategories = [$articleCategories[$i % count($articleCategories)]];

                if ($articleGroup === BlogArticleContentFactory::ARTICLE_GROUP_CARE && $i === 0) {
                    $assignedCategories = $articleCategories;
                }

                if ($articleGroup === BlogArticleContentFactory::ARTICLE_GROUP_TECHNOLOGY && $i === 1) {
                    $assignedCategories[] = $screenTechnologiesCategory;
                }

                $blogArticleData = $this->createArticle(
                    [$mainPageBlogCategory, ...$assignedCategories],
                    $articleGroup,
                    $i,
                );
                $this->blogArticleFacade->create($blogArticleData);
            }
        }
    }

    /**
     * @param array<string, \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]> $additionalCategoriesByArticleGroup
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    private function getGeneralArticleCategories(
        int $index,
        BlogCategory $mainPageBlogCategory,
        BlogCategory $inspirationCategory,
        array $additionalCategoriesByArticleGroup,
    ): array {
        $categoriesByTopicIndex = [
            null,
            null,
            $additionalCategoriesByArticleGroup[BlogArticleContentFactory::ARTICLE_GROUP_CARE][0],
            $additionalCategoriesByArticleGroup[BlogArticleContentFactory::ARTICLE_GROUP_TECHNOLOGY][0],
            $inspirationCategory,
        ];
        $articleCategory = $categoriesByTopicIndex[$index % self::ARTICLE_TITLE_TOPICS_COUNT];

        return $articleCategory === null ? [$mainPageBlogCategory] : [$mainPageBlogCategory, $articleCategory];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    private function getBuyingGuideArticleCategories(
        int $index,
        BlogCategory $mainPageBlogCategory,
        BlogCategory $buyingGuideCategory,
        BlogCategory $televisionsCategory,
        BlogCategory $audioCategory,
        BlogCategory $screenTechnologiesCategory,
    ): array {
        if ($index === 0) {
            return [$mainPageBlogCategory];
        }

        $articleCategories = [$buyingGuideCategory];
        $topicIndex = $index % self::ARTICLE_TITLE_TOPICS_COUNT;

        if ($topicIndex === 0) {
            $articleCategories = $index >= self::ARTICLE_TITLE_TOPICS_COUNT
                ? [$televisionsCategory, $screenTechnologiesCategory]
                : [$buyingGuideCategory, $televisionsCategory];
        } elseif ($topicIndex === 1) {
            $articleCategories[] = $audioCategory;
        }

        return [$mainPageBlogCategory, ...$articleCategories];
    }

    public static function getDemoBlogArticleUuid(int $articleNumber): string
    {
        return Uuid::uuid5(self::UUID_NAMESPACE, 'Blog article example ' . $articleNumber)->toString();
    }

    private function createSubcategory(BlogCategory $parentCategory, int $subcategoryOrder): BlogCategoryData
    {
        $blogCategoryData = $this->blogCategoryDataFactory->create();
        $blogCategoryData->parent = $parentCategory;

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $domainId = $domainConfig->getId();

            $translatedData = $this->getTranslatedSubcategoryData($subcategoryOrder, $locale);
            $blogCategoryData->seo[$domainId]->h1 = $translatedData['seoH1'];
            $blogCategoryData->seo[$domainId]->title = $translatedData['seoTitle'];
            $blogCategoryData->names[$locale] = $translatedData['name'];
            $blogCategoryData->descriptions[$locale] = $translatedData['description'];
            $blogCategoryData->seo[$domainId]->metaDescription = $translatedData['description'];
        }

        $blogCategoryData->uuid = Uuid::uuid5(
            self::UUID_NAMESPACE,
            $this->getSubcategoryUuidSeed($subcategoryOrder),
        )->toString();

        return $blogCategoryData;
    }

    /**
     * @return array{name: string, seoH1: string, seoTitle: string, description: string}
     */
    private function getTranslatedSubcategoryData(int $subcategoryOrder, string $locale): array
    {
        return $subcategoryOrder <= 5
            ? $this->getTranslatedTopLevelSubcategoryData($subcategoryOrder, $locale)
            : $this->getTranslatedNestedSubcategoryData($subcategoryOrder, $locale);
    }

    /**
     * @return array{name: string, seoH1: string, seoTitle: string, description: string}
     */
    private function getTranslatedTopLevelSubcategoryData(int $subcategoryOrder, string $locale): array
    {
        $parameters = ['%locale%' => $locale];

        return match ($subcategoryOrder) {
            1 => [
                'name' => t('First subsection %locale%', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoH1' => t('First subsection %locale% - h1', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoTitle' => t('title - First subsection %locale%', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'description' => t('description - First subsection %locale%', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            2 => [
                'name' => t('Second subsection %locale%', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoH1' => t('Second subsection %locale% - h1', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoTitle' => t('title - Second subsection %locale%', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'description' => t('description - Second subsection %locale%', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            3 => [
                'name' => t('Product news', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoH1' => t('Product news', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoTitle' => t('Product news | Demo shop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'description' => t('Discover new products, useful features, and updates from the world of electronics.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            4 => [
                'name' => t('Care and maintenance', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoH1' => t('Care and maintenance', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoTitle' => t('Care and maintenance | Demo shop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'description' => t('Simple advice to keep products working well and looking their best.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            5 => [
                'name' => t('Technology and trends', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoH1' => t('Technology and trends', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoTitle' => t('Technology and trends | Demo shop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'description' => t('Understand the technologies and trends shaping modern homes.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            default => throw new InvalidArgumentException(sprintf('Unknown top-level blog subcategory order "%d".', $subcategoryOrder)),
        };
    }

    /**
     * @return array{name: string, seoH1: string, seoTitle: string, description: string}
     */
    private function getTranslatedNestedSubcategoryData(int $subcategoryOrder, string $locale): array
    {
        return match ($subcategoryOrder) {
            6 => [
                'name' => t('Televisions and displays', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoH1' => t('Televisions and displays', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoTitle' => t('Televisions and displays | Demo shop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'description' => t('Guides for choosing television size, picture quality, and practical features.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            7 => [
                'name' => t('Audio and headphones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoH1' => t('Audio and headphones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoTitle' => t('Audio and headphones | Demo shop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'description' => t('Advice for comparing headphones, speakers, and home audio equipment.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            8 => [
                'name' => t('Screen technologies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoH1' => t('Screen technologies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoTitle' => t('Screen technologies | Demo shop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'description' => t('Understand OLED, QLED, Mini LED, resolution, HDR, and refresh rates.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            9 => [
                'name' => t('Cleaning and upkeep', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoH1' => t('Cleaning and upkeep', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'seoTitle' => t('Cleaning and upkeep | Demo shop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'description' => t('Safe cleaning routines and simple maintenance for everyday electronics.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            default => throw new InvalidArgumentException(sprintf('Unknown nested blog subcategory order "%d".', $subcategoryOrder)),
        };
    }

    private function getSubcategoryUuidSeed(int $subcategoryOrder): string
    {
        return match ($subcategoryOrder) {
            1 => 'First subsection',
            2 => 'Second subsection',
            3 => 'Product news',
            4 => 'Care and maintenance',
            5 => 'Technology and trends',
            6 => 'Televisions and displays',
            7 => 'Audio and headphones',
            8 => 'Screen technologies',
            9 => 'Cleaning and upkeep',
            default => throw new InvalidArgumentException(sprintf('Unknown blog subcategory order "%d".', $subcategoryOrder)),
        };
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $blogCategories
     */
    private function createArticle(array $blogCategories, string $articleGroup, int $index): BlogArticleData
    {
        $blogArticleData = $this->blogArticleDataFactory->create();

        $dateTime = (new DatePoint())->modify(sprintf('-%s days', $this->articleCounter + 3));

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $blogArticleData->publishDates[$domainId] = $dateTime->setTime(0, 0);
            $blogArticleData->statuses[$domainId] = BlogArticleStatusEnum::STATUS_PUBLISHED;
        }
        $blogArticleData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Blog article example ' . $this->articleCounter)->toString();

        /** @var array<string, string> $articleTitlesByLocale */
        $articleTitlesByLocale = [];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $articleTitle = $this->blogArticleContentFactory->createTitle($articleGroup, $index, $locale);
            $articleTitlesByLocale[$locale] = $articleTitle;
            $blogArticleData->names[$locale] = $articleTitle;
            $blogArticleData->descriptions[$locale] = $this->blogArticleContentFactory->createDescription(
                $articleTitle,
                $articleGroup,
                $locale,
            );
            $blogArticleData->perexes[$locale] = t('Clear advice and practical tips for %articleTitle%.', ['%articleTitle%' => $articleTitle], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $domainId = $domainConfig->getId();

            $blogArticleData->blogCategoriesByDomainId[$domainId] = $blogCategories;
            $blogArticleData->seo[$domainId]->title = t('%articleTitle% | Demo shop', ['%articleTitle%' => $articleTitlesByLocale[$locale]], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->seo[$domainId]->h1 = $articleTitlesByLocale[$locale];
            $blogArticleData->seo[$domainId]->metaDescription = t('Read practical advice in the article: %articleTitle%', ['%articleTitle%' => $articleTitlesByLocale[$locale]], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->assignBlogArticleAuthor($blogArticleData);

        $this->articleCounter++;

        return $blogArticleData;
    }

    private function assignBlogArticleAuthor(BlogArticleData $blogArticleData): void
    {
        if (crc32($blogArticleData->uuid) % 5 === 0) {
            return;
        }

        $authorReferenceNames = [
            BlogArticleAuthorDataFixture::BLOG_ARTICLE_AUTHOR_1,
            BlogArticleAuthorDataFixture::BLOG_ARTICLE_AUTHOR_2,
            BlogArticleAuthorDataFixture::BLOG_ARTICLE_AUTHOR_3,
        ];

        $blogArticleData->blogArticleAuthor = $this->getReference(
            $authorReferenceNames[$this->blogArticleAuthorRotation % count($authorReferenceNames)],
            BlogArticleAuthor::class,
        );
        $this->blogArticleAuthorRotation++;
    }

    private function applyStatusDiversity(BlogArticleData $blogArticleData, int $index): void
    {
        $domainIds = $this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds();

        if ($index === self::PAGES_IN_CATEGORY - 1) {
            foreach ($domainIds as $domainId) {
                $blogArticleData->statuses[$domainId] = BlogArticleStatusEnum::STATUS_DRAFT;
                $blogArticleData->publishDates[$domainId] = null;
            }
        } elseif ($index === self::PAGES_IN_CATEGORY - 2) {
            foreach ($domainIds as $domainId) {
                $blogArticleData->statuses[$domainId] = BlogArticleStatusEnum::STATUS_PREVIEW;
                $blogArticleData->publishDates[$domainId] = null;
            }
        } elseif ($index === self::PAGES_IN_CATEGORY - 3) {
            foreach ($domainIds as $domainId) {
                $blogArticleData->statuses[$domainId] = BlogArticleStatusEnum::STATUS_PUBLISHED;
                $blogArticleData->publishDates[$domainId] = (new DatePoint())->modify('+7 days')->setTime(10, 0);
            }
        }
    }

    private function createMainBlogArticle(
        BlogCategory $mainPageBlogCategory,
        BlogCategory $buyingGuideCategory,
    ): BlogArticle {
        $blogArticleData = $this->blogArticleDataFactory->create();
        $blogArticleData->blogArticleAuthor = $this->getReference(
            BlogArticleAuthorDataFixture::BLOG_ARTICLE_AUTHOR_1,
            BlogArticleAuthor::class,
        );

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $blogArticleData->publishDates[$domainId] = (new DatePoint())->modify('-1 day');
            $blogArticleData->statuses[$domainId] = BlogArticleStatusEnum::STATUS_PUBLISHED;
        }
        $firstDomainUrl = $this->domainsForDataFixtureProvider->getFirstAllowedDomainConfig()->getUrl();
        $blogArticleData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'GrapesJS page')->toString();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $articleTitle = t('How to choose the right TV for your living room', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->names[$locale] = $articleTitle;
            $blogArticleData->descriptions[$locale] = $this->blogArticleContentFactory->createMainArticleDescription(
                $firstDomainUrl,
            );
            $blogArticleData->perexes[$locale] = t('A practical guide to screen size, picture quality, connectivity, and the features worth considering before buying a new television.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();
            $articleTitle = $blogArticleData->names[$locale];
            $blogArticleData->blogCategoriesByDomainId[$domainId] = [
                $mainPageBlogCategory,
                $buyingGuideCategory,
            ];
            $blogArticleData->seo[$domainId]->title = t('%articleTitle% | Demo shop', ['%articleTitle%' => $articleTitle], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $blogArticleData->seo[$domainId]->h1 = $articleTitle;
            $blogArticleData->seo[$domainId]->metaDescription = $blogArticleData->perexes[$locale];
        }

        return $this->blogArticleFacade->create($blogArticleData);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
            BlogArticleAuthorDataFixture::class,
        ];
    }
}
