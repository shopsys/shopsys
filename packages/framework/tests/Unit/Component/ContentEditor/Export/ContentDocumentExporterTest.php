<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Export;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\ContentEditor\Export\ContentDocumentExporter;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyColumnsBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyImageBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyTextBlock;

class ContentDocumentExporterTest extends TestCase
{
    private ContentDocumentExporter $exporter;

    #[Override]
    protected function setUp(): void
    {
        $imageBlock = new #[ContentBlock(type: 'image')] class() extends DummyImageBlock {
            #[Override]
            public function export(array $data, int $domainId): array
            {
                $data['url'] = sprintf('https://example.com/%d/%s.jpg', $domainId, $data['mediaId']);

                return $data;
            }
        };

        $this->exporter = new ContentDocumentExporter(
            new ContentBlockRegistry([new DummyTextBlock(), $imageBlock, new DummyColumnsBlock()], []),
        );
    }

    public function testBlocksAreExportedWithChildrenRecursively(): void
    {
        $document = new ContentDocument(1, 'web', [
            new ContentBlockInstance('a', 'text', ['html' => '<p>Hello</p>']),
            new ContentBlockInstance('b', 'columns', ['layout' => '1/2-1/2'], [
                [new ContentBlockInstance('c', 'image', ['mediaId' => 7, 'alt' => ''])],
                [],
            ]),
        ]);

        $exported = $this->exporter->export($document, 2);

        $this->assertSame([
            'version' => 1,
            'profile' => 'web',
            'blocks' => [
                ['id' => 'a', 'type' => 'text', 'data' => ['html' => '<p>Hello</p>']],
                [
                    'id' => 'b',
                    'type' => 'columns',
                    'data' => ['layout' => '1/2-1/2'],
                    'children' => [
                        [['id' => 'c', 'type' => 'image', 'data' => ['mediaId' => 7, 'alt' => '', 'url' => 'https://example.com/2/7.jpg']]],
                        [],
                    ],
                ],
            ],
        ], $exported);
    }

    public function testUnknownBlockTypesAreSkipped(): void
    {
        $document = new ContentDocument(1, 'web', [
            new ContentBlockInstance('a', 'removed-type', ['html' => 'x']),
            new ContentBlockInstance('b', 'text', ['html' => 'y']),
        ]);

        $exported = $this->exporter->export($document, 1);

        $this->assertSame(['b'], array_column($exported['blocks'], 'id'));
    }

    public function testEmptyDocumentIsExportedWithoutBlocks(): void
    {
        $exported = $this->exporter->export(new ContentDocument(1, 'mail', []), 1);

        $this->assertSame(['version' => 1, 'profile' => 'mail', 'blocks' => []], $exported);
    }
}
