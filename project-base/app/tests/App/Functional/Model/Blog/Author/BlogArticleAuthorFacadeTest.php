<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Blog\Author;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class BlogArticleAuthorFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private BlogArticleAuthorFacade $blogArticleAuthorFacade;

    /**
     * @inject
     */
    private BlogArticleAuthorDataFactory $blogArticleAuthorDataFactory;

    /**
     * @inject
     */
    private BlogArticleFacade $blogArticleFacade;

    /**
     * @inject
     */
    private BlogArticleDataFactory $blogArticleDataFactory;

    public function testCreateAndEditBlogArticleAuthor(): void
    {
        $blogArticleAuthorData = $this->blogArticleAuthorDataFactory->create();
        $blogArticleAuthorData->name = 'Original name';

        foreach (array_keys($blogArticleAuthorData->jobTitles) as $locale) {
            $blogArticleAuthorData->jobTitles[$locale] = 'Original role';
            $blogArticleAuthorData->descriptions[$locale] = 'Original description';
        }

        $blogArticleAuthor = $this->blogArticleAuthorFacade->create($blogArticleAuthorData);
        $blogArticleAuthorId = $blogArticleAuthor->getId();

        $this->assertSame('Original name', $blogArticleAuthor->getName());
        $this->assertSame('Original role', $blogArticleAuthor->getJobTitle());
        $this->assertSame('Original description', $blogArticleAuthor->getDescription());

        $editData = $this->blogArticleAuthorDataFactory->createFromBlogArticleAuthor($blogArticleAuthor);
        $editData->name = 'Edited name';
        $this->blogArticleAuthorFacade->edit($blogArticleAuthorId, $editData);

        $editedBlogArticleAuthor = $this->blogArticleAuthorFacade->getById($blogArticleAuthorId);
        $this->assertSame('Edited name', $editedBlogArticleAuthor->getName());
    }

    public function testDeletingAuthorUnsetsItOnBlogArticle(): void
    {
        $blogArticleAuthorData = $this->blogArticleAuthorDataFactory->create();
        $blogArticleAuthorData->name = 'Author to delete';
        $blogArticleAuthor = $this->blogArticleAuthorFacade->create($blogArticleAuthorData);

        $blogArticle = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_ARTICLE, BlogArticle::class);
        $blogArticleData = $this->blogArticleDataFactory->createFromBlogArticle($blogArticle);
        $blogArticleData->blogArticleAuthor = $blogArticleAuthor;
        $this->blogArticleFacade->edit($blogArticle->getId(), $blogArticleData);

        $this->em->refresh($blogArticle);
        $this->assertNotNull($blogArticle->getBlogArticleAuthor());

        $this->blogArticleAuthorFacade->deleteById($blogArticleAuthor->getId());

        $this->em->refresh($blogArticle);
        $this->assertNull($blogArticle->getBlogArticleAuthor());
    }
}
