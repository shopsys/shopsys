<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Document;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;

class ContentDocumentTest extends TestCase
{
    public function testToJsonKeepsUnicodeAndSlashesUnescaped(): void
    {
        $document = new ContentDocument(1, 'web', [new ContentBlockInstance('a', 'text', ['html' => 'č/ž'])]);

        $this->assertSame('{"version":1,"profile":"web","blocks":[{"id":"a","type":"text","data":{"html":"č/ž"}}]}', $document->toJson());
    }

    public function testWithBlocksReturnsNewDocumentAndKeepsOriginal(): void
    {
        $document = new ContentDocument(1, 'web', []);
        $block = new ContentBlockInstance('a', 'text', []);

        $result = $document->withBlocks([$block]);

        $this->assertTrue($document->isEmpty());
        $this->assertFalse($result->isEmpty());
        $this->assertSame([$block], $result->blocks);
    }
}
