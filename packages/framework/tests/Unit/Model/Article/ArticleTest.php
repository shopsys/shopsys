<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Article;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\GrapesJs\EnsureCorrectGrapesJsFormatHelper;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Article\ArticleFactory;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Clock\MockClock;

class ArticleTest extends TestCase
{
    public function testEditingPreservesPublicationDateAndUpdatesModificationDate(): void
    {
        $previousClock = Clock::get();
        $clock = new MockClock('2026-09-01T10:00:00+00:00');
        Clock::set($clock);

        try {
            $data = $this->createArticleData();
            $data->createdAt = new DatePoint('2026-08-01T10:00:00+00:00');
            $data->publishDate = new DatePoint('2026-08-15T10:00:00+00:00');
            $article = $this->createArticle($data);
            $createdAt = $article->getCreatedAt();
            $publishedAt = $article->getPublishDate();

            $clock->sleep(3600);
            $data->text = 'Updated content';
            $article->edit($data);

            $this->assertSame($createdAt, $article->getCreatedAt());
            $this->assertSame($publishedAt, $article->getPublishDate());
            $this->assertEquals($clock->now(), $article->getModifiedAt());
            $this->assertNotEquals($createdAt, $article->getPublishDate());
        } finally {
            Clock::set($previousClock);
        }
    }

    public function testUnknownPublicationDateIsNotReplacedWithCreationDate(): void
    {
        $article = $this->createArticle($this->createArticleData());

        $this->assertNull($article->getPublishDate());
        $this->assertNotNull($article->getModifiedAt());
    }

    private function createArticleData(): ArticleData
    {
        $factory = new ArticleDataFactory(
            $this->createStub(FriendlyUrlFacade::class),
            $this->createStub(Domain::class),
            $this->createStub(ImageUploadDataFactory::class),
            $this->createStub(EnsureCorrectGrapesJsFormatHelper::class),
        );

        return $factory->create(1);
    }

    private function createArticle(ArticleData $data): Article
    {
        $entityNameResolver = $this->createStub(EntityNameResolver::class);
        $entityNameResolver->method('resolve')->willReturn(Article::class);
        $factory = new ArticleFactory($entityNameResolver);

        return $factory->create($data);
    }
}
