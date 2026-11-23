<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Blog;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Override;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportQueueFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Tests\App\Performance\Page\QueryCountingMiddleware;
use Tests\App\Test\TableRowTestHelper;
use Tests\App\Test\TransactionFunctionalTestCase;

final class BlogCategoryFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @inject
     */
    private BlogArticleExportQueueFacade $blogArticleExportQueueFacade;

    /**
     * @inject
     */
    private BlogArticleFacade $blogArticleFacade;

    /**
     * @inject
     */
    private TableRowTestHelper $tableRowTestHelper;

    /**
     * @var array<int, int[]>
     */
    private array $blogArticleIdsQueuedBeforeTestIndexedByDomainId;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->blogArticleIdsQueuedBeforeTestIndexedByDomainId = $this->popQueuedBlogArticleIdsIndexedByDomainId();
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->popQueuedBlogArticleIdsIndexedByDomainId();

        foreach ($this->blogArticleIdsQueuedBeforeTestIndexedByDomainId as $domainId => $blogArticleIds) {
            if ($blogArticleIds !== []) {
                $this->blogArticleExportQueueFacade->addIdsBatch($blogArticleIds, $domainId);
            }
        }

        parent::tearDown();
    }

    public function testReorderByUnchangedNestedSetValuesDoesNotRewriteBlogCategoriesNorExportBlogArticles(): void
    {
        $blogCategoriesOrderingData = $this->getCurrentBlogCategoriesOrderingData();
        $rowVersionsBeforeReorder = $this->getBlogCategoryRowVersions();

        $this->blogCategoryFacade->reorderByNestedSetValues($blogCategoriesOrderingData);

        $this->assertSame($rowVersionsBeforeReorder, $this->getBlogCategoryRowVersions());
        $this->assertSame([], array_merge(...array_values($this->popQueuedBlogArticleIdsIndexedByDomainId())));
    }

    public function testReorderByChangedNestedSetValuesExportsBlogArticlesOfAllDomains(): void
    {
        $movedBlogCategory = $this->getReference(BlogArticleDataFixture::BLOG_CATEGORY_SCREEN_TECHNOLOGIES, BlogCategory::class);
        $newParentBlogCategory = $this->getReference(BlogArticleDataFixture::SECOND_DEMO_BLOG_SUBCATEGORY, BlogCategory::class);
        $blogCategoriesOrderingData = $this->getCurrentBlogCategoriesOrderingData();
        $blogCategoriesOrderingData[$movedBlogCategory->getId()]['parent_id'] = $newParentBlogCategory->getId();
        $queryCountingMiddleware = QueryCountingMiddleware::createInjectedInto($this->em->getConnection());

        $this->blogCategoryFacade->reorderByNestedSetValues($blogCategoriesOrderingData);

        $this->assertSame($this->getAllBlogArticleIdsIndexedByDomainId(), $this->popQueuedBlogArticleIdsIndexedByDomainId());
        $this->assertCount(1, $queryCountingMiddleware->getExecutedQueriesMatching('~^\s*UPDATE blog_categories\b~'));
    }

    /**
     * @return array<int, int[]>
     */
    private function popQueuedBlogArticleIdsIndexedByDomainId(): array
    {
        $domainIds = $this->domain->getAllIds();
        $queuedBlogArticleIdsIndexedByDomainId = [];

        foreach ($domainIds as $domainId) {
            $queuedBlogArticleIds = array_map(
                'intval',
                $this->blogArticleExportQueueFacade->getIds($domainId, max(1, $this->blogArticleExportQueueFacade->getCount($domainId))),
            );
            sort($queuedBlogArticleIds);
            $queuedBlogArticleIdsIndexedByDomainId[$domainId] = $queuedBlogArticleIds;
        }

        return $queuedBlogArticleIdsIndexedByDomainId;
    }

    /**
     * @return array<int, int[]>
     */
    private function getAllBlogArticleIdsIndexedByDomainId(): array
    {
        $domainIds = $this->domain->getAllIds();
        $blogArticleIdsIndexedByDomainId = [];

        foreach ($domainIds as $domainId) {
            $blogArticleIds = array_values(array_unique($this->blogArticleFacade->getAllIdsByDomainId($domainId)));
            sort($blogArticleIds);
            $blogArticleIdsIndexedByDomainId[$domainId] = $blogArticleIds;
        }

        return $blogArticleIdsIndexedByDomainId;
    }

    /**
     * @return array<int, array{id: int, parent_id: int|null, depth: int, left: int, right: int}>
     */
    private function getCurrentBlogCategoriesOrderingData(): array
    {
        $blogCategories = $this->em->getRepository(BlogCategory::class)->findBy([], ['lft' => 'ASC']);
        $blogCategoriesOrderingData = [];

        foreach ($blogCategories as $blogCategory) {
            $blogCategoriesOrderingData[$blogCategory->getId()] = [
                'id' => $blogCategory->getId(),
                'parent_id' => $blogCategory->getParent()?->getId(),
                'depth' => $blogCategory->getLevel(),
                'left' => $blogCategory->getLft(),
                'right' => $blogCategory->getRgt(),
            ];
        }

        return $blogCategoriesOrderingData;
    }

    /**
     * @return array<string, string>
     */
    private function getBlogCategoryRowVersions(): array
    {
        return $this->tableRowTestHelper->getRowVersionsIndexedByPrimaryKey('blog_categories', ['id']);
    }
}
