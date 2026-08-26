<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Blog\Category;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportQueueFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class BlogCategoryFacadeTest extends TransactionFunctionalTestCase
{
    private const int EXPORT_QUEUE_READ_LIMIT = 10000;

    /**
     * @inject
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @inject
     */
    private BlogCategoryDataFactory $blogCategoryDataFactory;

    /**
     * @inject
     */
    private BlogArticleFacade $blogArticleFacade;

    /**
     * @inject
     */
    private BlogArticleExportQueueFacade $blogArticleExportQueueFacade;

    public function testEditingBlogCategorySchedulesArticlesOfItsDescendantsToExportOnAllDomains(): void
    {
        $editedBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class);
        $descendantBlogCategory = $this->getReference(BlogArticleDataFixture::BLOG_CATEGORY_SCREEN_TECHNOLOGIES, BlogCategory::class);

        $expectedBlogArticleIdsByDomainId = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $expectedBlogArticleIdsByDomainId[$domainConfig->getId()] = $this->blogArticleFacade->getBlogArticleIdsByCategory(
                $descendantBlogCategory,
                $domainConfig->getId(),
                $domainConfig->getLocale(),
            );
            $this->assertNotEmpty($expectedBlogArticleIdsByDomainId[$domainConfig->getId()]);

            $this->blogArticleExportQueueFacade->getIds($domainConfig->getId(), self::EXPORT_QUEUE_READ_LIMIT);
        }

        $this->blogCategoryFacade->edit(
            $editedBlogCategory->getId(),
            $this->blogCategoryDataFactory->createFromBlogCategory($editedBlogCategory),
        );

        foreach ($this->domain->getAll() as $domainConfig) {
            $scheduledBlogArticleIds = array_map(
                'intval',
                $this->blogArticleExportQueueFacade->getIds($domainConfig->getId(), self::EXPORT_QUEUE_READ_LIMIT),
            );

            foreach ($expectedBlogArticleIdsByDomainId[$domainConfig->getId()] as $expectedBlogArticleId) {
                $this->assertContains(
                    $expectedBlogArticleId,
                    $scheduledBlogArticleIds,
                    sprintf('Blog article ID %d from a descendant blog category was not scheduled to export on domain ID %d.', $expectedBlogArticleId, $domainConfig->getId()),
                );
            }
        }
    }
}
