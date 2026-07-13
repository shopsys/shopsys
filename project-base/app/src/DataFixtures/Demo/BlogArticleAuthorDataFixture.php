<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorFacade;

class BlogArticleAuthorDataFixture extends AbstractReferenceFixture
{
    private const string UUID_NAMESPACE = '2b3c7b6a-1d8e-4d2a-9f0b-3c5e7d9a1b2c';

    public const string BLOG_ARTICLE_AUTHOR_1 = 'blog_article_author_1';
    public const string BLOG_ARTICLE_AUTHOR_2 = 'blog_article_author_2';
    public const string BLOG_ARTICLE_AUTHOR_3 = 'blog_article_author_3';

    public function __construct(
        private readonly BlogArticleAuthorFacade $blogArticleAuthorFacade,
        private readonly BlogArticleAuthorDataFactory $blogArticleAuthorDataFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->createBlogArticleAuthor(
            self::BLOG_ARTICLE_AUTHOR_1,
            'Amelia Hartwood',
            'Senior content strategist',
            'Amelia has spent more than a decade writing about e-commerce trends and helping brands tell their story online.',
        );

        $this->createBlogArticleAuthor(
            self::BLOG_ARTICLE_AUTHOR_2,
            'Diego Fernández',
            'Consumer technology reviewer',
            null,
        );

        $this->createBlogArticleAuthor(
            self::BLOG_ARTICLE_AUTHOR_3,
            'Nina Kovalenko',
            null,
            'Nina covers home, lifestyle and the small design details that make everyday products better.',
        );
    }

    private function createBlogArticleAuthor(
        string $referenceName,
        string $name,
        ?string $jobTitle,
        ?string $description,
    ): void {
        $blogArticleAuthorData = $this->blogArticleAuthorDataFactory->create();
        $blogArticleAuthorData->name = $name;
        $blogArticleAuthorData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $name)->toString();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $blogArticleAuthorData->jobTitles[$locale] = $jobTitle;
            $blogArticleAuthorData->descriptions[$locale] = $description;
        }

        $blogArticleAuthor = $this->blogArticleAuthorFacade->create($blogArticleAuthorData);
        $this->addReference($referenceName, $blogArticleAuthor);
    }
}
