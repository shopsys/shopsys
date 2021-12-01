<?php

declare(strict_types=1);

namespace Tests\App\Unit\Model\Article;

use App\Model\Article\Article;
use App\Model\Article\ArticleData;
use DateTime;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testValidationOfArticleAttributeExtension()
    {
        $articleData = new ArticleData();
        $articleData->createdAt = new Datetime('2000-01-01');

        $article = new Article($articleData);

        $this->assertEquals(new Datetime('2000-01-01'), $article->getCreatedAt());
    }
}
