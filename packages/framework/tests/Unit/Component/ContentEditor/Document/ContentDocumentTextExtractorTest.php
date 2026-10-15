<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Document;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentTextExtractor;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class ContentDocumentTextExtractorTest extends TestCase
{
    public function testTextIsExtractedFromBlocksAndChildrenWithoutMarkup(): void
    {
        $extractor = new ContentDocumentTextExtractor(ContentEditorTestFactory::createRegistry());
        $document = new ContentDocument(1, 'web', [
            new ContentBlockInstance('a', 'text', ['html' => '<p>Hello <strong>wor</strong>ld</p><p>Fish &amp; chips&nbsp;{customer_name}</p>']),
            new ContentBlockInstance('b', 'image', ['mediaId' => 5, 'alt' => 'A cat']),
            new ContentBlockInstance('c', 'columns', ['layout' => '1/2-1/2'], [
                [new ContentBlockInstance('d', 'text', ['html' => '<ul><li>one</li><li>two</li></ul>'])],
                [],
            ]),
            new ContentBlockInstance('e', 'unknown', ['html' => 'ignored']),
        ]);

        $text = $extractor->extractText($document);

        $this->assertSame("Hello world\nFish & chips {customer_name}\nA cat\none\ntwo", $text);
    }

    public function testEmptyDocumentHasNoText(): void
    {
        $extractor = new ContentDocumentTextExtractor(ContentEditorTestFactory::createRegistry());

        $this->assertSame('', $extractor->extractText(new ContentDocument(1, 'web', [])));
    }
}
