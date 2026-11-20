<?php

declare(strict_types=1);

namespace Tests\App\Functional\Tool\Blog;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Mcp\Exception\ToolCallException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\McpBundle\Tool\Blog\BlogArticleChangeLabel;
use Shopsys\McpBundle\Tool\Blog\BlogArticleMcpFacade;
use Shopsys\McpBundle\Tool\Blog\EditBlogArticleArguments;
use Shopsys\McpBundle\Tool\Blog\EditPublishedBlogArticleTool;
use Shopsys\McpBundle\Tool\Blog\EditUnpublishedBlogArticleTool;
use Tests\App\Test\TransactionFunctionalTestCase;

class BlogArticleMcpFacadeTest extends TransactionFunctionalTestCase
{
    private const string DESCRIPTION = '<p>Intro sentence.</p><p>Old sentence.</p><p>Outro.</p>';

    /**
     * @inject
     */
    private BlogArticleMcpFacade $blogArticleMcpFacade;

    /**
     * @inject
     */
    private EditPublishedBlogArticleTool $editPublishedBlogArticleTool;

    /**
     * @inject
     */
    private EditUnpublishedBlogArticleTool $editUnpublishedBlogArticleTool;

    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    public function testCreatedArticleIsDraftOnAllDomains(): void
    {
        $blogArticle = $this->blogArticleMcpFacade->create([$this->getFirstDomainLocale() => 'New article']);

        $this->assertSame('New article', $blogArticle->getName($this->getFirstDomainLocale()));
        $this->assertTrue($blogArticle->isDraftOnAllDomains());
    }

    public function testDescribeEditListsOnlyChangedFields(): void
    {
        $blogArticle = $this->getPublishedBlogArticle();
        $locale = $this->getFirstDomainLocale();
        $currentName = $blogArticle->getName($locale);

        $change = $this->blogArticleMcpFacade->describeEdit(new EditBlogArticleArguments(
            blogArticleId: $blogArticle->getId(),
            names: [$locale => $currentName],
            perexes: [$locale => 'New perex'],
        ))->toArray();

        $this->assertCount(1, $change['fields']);
        $this->assertSame(BlogArticleChangeLabel::PEREX, $change['fields'][0]['label']);
        $this->assertSame(['%locale%' => $locale], $change['fields'][0]['labelParameters']);
        $this->assertSame('New perex', $change['fields'][0]['after']);
    }

    public function testDescribeEditFailsWhenInputDoesNotChangeArticle(): void
    {
        $blogArticle = $this->getPublishedBlogArticle();
        $locale = $this->getFirstDomainLocale();

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('The input does not change the blog article.');

        $this->blogArticleMcpFacade->describeEdit(new EditBlogArticleArguments(
            blogArticleId: $blogArticle->getId(),
            names: [$locale => $blogArticle->getName($locale)],
        ));
    }

    public function testDescribeEditShowsSlugChange(): void
    {
        $blogArticle = $this->getPublishedBlogArticle();
        $currentSlug = $this->friendlyUrlFacade->getMainFriendlyUrlSlug(Domain::FIRST_DOMAIN_ID, 'front_blogarticle_detail', $blogArticle->getId());

        $change = $this->blogArticleMcpFacade->describeEdit(new EditBlogArticleArguments(
            blogArticleId: $blogArticle->getId(),
            slugsByDomainId: [Domain::FIRST_DOMAIN_ID => 'new-slug'],
        ))->toArray();

        $this->assertSame(BlogArticleChangeLabel::SLUG, $change['fields'][0]['label']);
        $this->assertSame($currentSlug, $change['fields'][0]['before']);
        $this->assertSame('new-slug', $change['fields'][0]['after']);
    }

    public function testDescribeEditShowsReplacementWithItsContext(): void
    {
        $blogArticle = $this->getPublishedBlogArticleWithDescription();
        $locale = $this->getFirstDomainLocale();

        $change = $this->blogArticleMcpFacade->describeEdit(new EditBlogArticleArguments(
            blogArticleId: $blogArticle->getId(),
            descriptionReplacements: [$locale => [['find' => '<p>Old sentence.</p>', 'replace' => '<p>New sentence.</p>']]],
        ))->toArray();

        $this->assertSame([
            'label' => BlogArticleChangeLabel::CONTENT,
            'labelParameters' => ['%locale%' => $locale],
            'before' => '<p>Old sentence.</p>',
            'after' => '<p>New sentence.</p>',
            'contextBefore' => '<p>Intro sentence.</p>',
            'contextAfter' => '<p>Outro.</p>',
        ], $change['fields'][0]);
    }

    public function testEditReplacesOnlyGivenPartsOfDescriptionInOrder(): void
    {
        $blogArticle = $this->getPublishedBlogArticleWithDescription();
        $locale = $this->getFirstDomainLocale();

        $this->blogArticleMcpFacade->edit(new EditBlogArticleArguments(
            blogArticleId: $blogArticle->getId(),
            descriptionReplacements: [$locale => [
                ['find' => 'Old sentence.', 'replace' => 'New sentence.'],
                ['find' => 'New sentence.', 'replace' => 'Newer sentence.'],
            ]],
        ));

        $this->assertSame('<div class="gjs-text-ckeditor"><p>Intro sentence.</p><p>Newer sentence.</p><p>Outro.</p></div>', $blogArticle->getDescription($locale));
    }

    public function testEditFailsWhenTextToFindIsMissing(): void
    {
        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('The text to find was not found in the content');

        $this->editDescriptionWithReplacement('Missing sentence.');
    }

    public function testEditFailsWhenTextToFindIsNotUnique(): void
    {
        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('The text to find is in the content (' . $this->getFirstDomainLocale() . ') 3 times');

        $this->editDescriptionWithReplacement('<p>');
    }

    public function testEditFailsWhenDescriptionIsGivenWholeAndAsReplacement(): void
    {
        $blogArticle = $this->getPublishedBlogArticleWithDescription();
        $locale = $this->getFirstDomainLocale();

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('cannot be given in both descriptions and descriptionReplacements');

        $this->blogArticleMcpFacade->edit(new EditBlogArticleArguments(
            blogArticleId: $blogArticle->getId(),
            descriptions: [$locale => '<p>Whole content.</p>'],
            descriptionReplacements: [$locale => [['find' => 'Old sentence.', 'replace' => 'New sentence.']]],
        ));
    }

    public function testEditFailsForUnknownLocale(): void
    {
        $blogArticle = $this->getPublishedBlogArticle();

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('The locale "xx" does not exist');

        $this->blogArticleMcpFacade->edit(new EditBlogArticleArguments(blogArticleId: $blogArticle->getId(), names: ['xx' => 'Name']));
    }

    public function testEditFailsForUnknownBlogCategory(): void
    {
        $blogArticle = $this->getPublishedBlogArticle();

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Blog categories with IDs 999999 do not exist.');

        $this->blogArticleMcpFacade->edit(new EditBlogArticleArguments(
            blogArticleId: $blogArticle->getId(),
            blogCategoryIdsByDomainId: [Domain::FIRST_DOMAIN_ID => [999999]],
        ));
    }

    public function testEditUnpublishedToolRejectsPublishedArticle(): void
    {
        $blogArticle = $this->getPublishedBlogArticle();

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('edit it with the tool editPublishedBlogArticle');

        $this->editUnpublishedBlogArticleTool->editUnpublishedBlogArticle($blogArticle->getId(), perexes: [$this->getFirstDomainLocale() => 'New perex']);
    }

    public function testEditUnpublishedToolMakesSlugMainUrl(): void
    {
        $blogArticle = $this->getReference(BlogArticleDataFixture::BLOG_ARTICLE_DRAFT, BlogArticle::class);

        $this->editUnpublishedBlogArticleTool->editUnpublishedBlogArticle($blogArticle->getId(), slugsByDomainId: [Domain::FIRST_DOMAIN_ID => 'new-draft-slug']);

        $this->assertSame('new-draft-slug', $this->friendlyUrlFacade->getMainFriendlyUrlSlug(Domain::FIRST_DOMAIN_ID, 'front_blogarticle_detail', $blogArticle->getId()));
    }

    public function testEditFailsForSlugOfAnotherArticle(): void
    {
        $draftBlogArticle = $this->getReference(BlogArticleDataFixture::BLOG_ARTICLE_DRAFT, BlogArticle::class);
        $usedSlug = $this->friendlyUrlFacade->getMainFriendlyUrlSlug(Domain::FIRST_DOMAIN_ID, 'front_blogarticle_detail', $this->getPublishedBlogArticle()->getId());

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('is already used on the domain');

        $this->blogArticleMcpFacade->edit(new EditBlogArticleArguments(
            blogArticleId: $draftBlogArticle->getId(),
            slugsByDomainId: [Domain::FIRST_DOMAIN_ID => $usedSlug],
        ));
    }

    public function testEditFailsForInvalidSlug(): void
    {
        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('is not valid');

        $this->blogArticleMcpFacade->edit(new EditBlogArticleArguments(
            blogArticleId: $this->getPublishedBlogArticle()->getId(),
            slugsByDomainId: [Domain::FIRST_DOMAIN_ID => 'Nový článek'],
        ));
    }

    public function testEditPublishedToolRejectsDraftArticle(): void
    {
        $blogArticle = $this->getReference(BlogArticleDataFixture::BLOG_ARTICLE_DRAFT, BlogArticle::class);

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('edit it with the tool editUnpublishedBlogArticle');

        $this->editPublishedBlogArticleTool->describeChange(new EditBlogArticleArguments(
            blogArticleId: $blogArticle->getId(),
            perexes: [$this->getFirstDomainLocale() => 'New perex'],
        ));
    }

    private function editDescriptionWithReplacement(string $find): void
    {
        $blogArticle = $this->getPublishedBlogArticleWithDescription();

        $this->blogArticleMcpFacade->edit(new EditBlogArticleArguments(
            blogArticleId: $blogArticle->getId(),
            descriptionReplacements: [$this->getFirstDomainLocale() => [['find' => $find, 'replace' => 'Replacement']]],
        ));
    }

    private function getPublishedBlogArticleWithDescription(): BlogArticle
    {
        $blogArticle = $this->getPublishedBlogArticle();

        return $this->blogArticleMcpFacade->edit(new EditBlogArticleArguments(
            blogArticleId: $blogArticle->getId(),
            descriptions: [$this->getFirstDomainLocale() => self::DESCRIPTION],
        ));
    }

    private function getPublishedBlogArticle(): BlogArticle
    {
        return $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_ARTICLE, BlogArticle::class);
    }
}
