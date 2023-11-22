<?php

declare(strict_types=1);

namespace Tests\App\Unit\Model\Article;

use App\Model\Article\Article;
use App\Model\Article\ArticleData;
use DateTime;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ArticleTest extends TestCase
{
    public function testValidationOfArticleAttributeExtension(): void
    {
        $articleData = new ArticleData();
        $articleData->createdAt = new Datetime('2000-01-01');
        $articleData->domainId = Domain::FIRST_DOMAIN_ID;
        $articleData->name = 'Demonstrative name';
        $articleData->placement = Article::PLACEMENT_NONE;

        $article = new Article($articleData);

        $this->assertEquals(new Datetime('2000-01-01'), $article->getCreatedAt());
    }
}
