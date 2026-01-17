<?php

declare(strict_types=1);

namespace Tests\App\Unit\Model\Article;

use App\Model\Article\Article;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Symfony\Component\Clock\DatePoint;

class ArticleTest extends TestCase
{
    public function testValidationOfArticleAttributeExtension(): void
    {
        $articleData = new ArticleData();
        $articleData->createdAt = new DatePoint('2000-01-01');
        $articleData->domainId = Domain::FIRST_DOMAIN_ID;
        $articleData->name = 'Demonstrative name';
        $articleData->placement = Article::PLACEMENT_NONE;

        $article = new Article($articleData);

        $this->assertEquals(new DatePoint('2000-01-01'), $article->getCreatedAt());
    }
}
