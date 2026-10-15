<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Document;

use Override;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentNormalizer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\Enum\InvalidEnumCaseException;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyNormalizedBlock;

class ContentDocumentNormalizerTest extends TestCase
{
    private const string UUID = '4d7f2f2e-5b0c-4a58-9a3c-6a1a5c2e8f10';

    private ContentDocumentNormalizer $normalizer;

    #[Override]
    protected function setUp(): void
    {
        $this->normalizer = new ContentDocumentNormalizer(
            ContentEditorTestFactory::createRegistry(),
            ContentEditorTestFactory::createSanitizer(),
            new ContentProfileEnum(),
        );
    }

    public function testUnknownProfileThrows(): void
    {
        $this->expectException(InvalidEnumCaseException::class);

        $this->normalizer->normalize(new ContentDocument(1, 'print', []));
    }

    public function testBlockOfUnknownTypeIsDroppedAndReported(): void
    {
        $document = $this->createDocument([new ContentBlockInstance(self::UUID, 'unknown', [])]);

        $result = $this->normalizer->normalize($document);

        $this->assertTrue($result->isEmpty());
        $this->assertSame(['unknown'], $this->normalizer->getLastReport()->getDroppedBlockTypes());
    }

    public function testBlockNotSupportingDocumentProfileIsDroppedAndReported(): void
    {
        $document = $this->createDocument([new ContentBlockInstance(self::UUID, 'mail-text', [])]);

        $result = $this->normalizer->normalize($document);

        $this->assertTrue($result->isEmpty());
        $this->assertSame(['mail-text'], $this->normalizer->getLastReport()->getDroppedBlockTypes());
    }

    public function testMissingOrInvalidIdIsGeneratedAndValidIdKept(): void
    {
        $document = $this->createDocument([
            new ContentBlockInstance('', 'text', []),
            new ContentBlockInstance('not-a-uuid', 'text', []),
            new ContentBlockInstance(self::UUID, 'text', []),
        ]);

        $result = $this->normalizer->normalize($document);

        $this->assertTrue(Uuid::isValid($result->blocks[0]->id));
        $this->assertTrue(Uuid::isValid($result->blocks[1]->id));
        $this->assertNotSame($result->blocks[0]->id, $result->blocks[1]->id);
        $this->assertSame(self::UUID, $result->blocks[2]->id);
    }

    public function testDataKeepsOnlyDeclaredKeysAndFillsDefaults(): void
    {
        $document = $this->createDocument([
            new ContentBlockInstance(self::UUID, 'image', ['mediaId' => '12', 'foreign' => 'x']),
        ]);

        $result = $this->normalizer->normalize($document);

        $this->assertSame(['mediaId' => 12, 'alt' => '', 'position' => 'center'], $result->blocks[0]->data);
    }

    public function testWysiwygValuesAreSanitized(): void
    {
        $document = $this->createDocument([
            new ContentBlockInstance(self::UUID, 'text', ['html' => '<p onclick="x()">Hi</p><script>alert(1)</script>']),
        ]);

        $result = $this->normalizer->normalize($document);

        $this->assertSame('<p>Hi</p>', $result->blocks[0]->data['html']);
    }

    public function testTableCellsAreSanitized(): void
    {
        $document = $this->createDocument([
            new ContentBlockInstance(self::UUID, 'table', ['cells' => [['<p onclick="x()">A</p>', '<script>x</script>B'], ['C']]]),
        ]);

        $result = $this->normalizer->normalize($document);

        $this->assertSame([['<p>A</p>', 'B'], ['C']], $result->blocks[0]->data['cells']);
    }

    public function testUrlKeyIsKeptOnlyOnBlocksWithMediaField(): void
    {
        $document = $this->createDocument([
            new ContentBlockInstance(self::UUID, 'image', ['url' => '/legacy.png']),
            new ContentBlockInstance(self::UUID, 'text', ['url' => '/legacy.png']),
        ]);

        $result = $this->normalizer->normalize($document);

        $this->assertSame('/legacy.png', $result->blocks[0]->data['url']);
        $this->assertArrayNotHasKey('url', $result->blocks[1]->data);
    }

    public function testBlockNormalizesDataAfterFieldsAreNormalized(): void
    {
        $normalizer = new ContentDocumentNormalizer(
            new ContentBlockRegistry([new DummyNormalizedBlock()], []),
            ContentEditorTestFactory::createSanitizer(),
            new ContentProfileEnum(),
        );
        $document = $this->createDocument([new ContentBlockInstance(self::UUID, 'normalized', ['text' => 'abc', 'unknown' => 1])]);

        $result = $normalizer->normalize($document);

        $this->assertSame(['text' => 'abc', 'textLength' => 3], $result->blocks[0]->data);
    }

    public function testNonContainerBlockLosesChildren(): void
    {
        $document = $this->createDocument([
            new ContentBlockInstance(self::UUID, 'text', [], [[new ContentBlockInstance(self::UUID, 'text', [])]]),
        ]);

        $result = $this->normalizer->normalize($document);

        $this->assertNull($result->blocks[0]->children);
    }

    public function testContainerGetsMissingSlotsAddedEmpty(): void
    {
        $document = $this->createDocument([new ContentBlockInstance(self::UUID, 'columns', ['layout' => '1/3-1/3-1/3'])]);

        $result = $this->normalizer->normalize($document);

        $this->assertSame([[], [], []], $result->blocks[0]->children);
    }

    public function testBlocksOfExtraSlotsAreAppendedToLastSlot(): void
    {
        $first = new ContentBlockInstance(self::UUID, 'text', ['html' => '1']);
        $second = new ContentBlockInstance(self::UUID, 'text', ['html' => '2']);
        $third = new ContentBlockInstance(self::UUID, 'text', ['html' => '3']);
        $document = $this->createDocument([
            new ContentBlockInstance(self::UUID, 'columns', ['layout' => '1/2-1/2'], [[$first], [$second], [$third]]),
        ]);

        $result = $this->normalizer->normalize($document);

        $children = $result->blocks[0]->children;
        $this->assertCount(2, $children);
        $this->assertSame(['1'], array_map(static fn (ContentBlockInstance $block) => $block->data['html'], $children[0]));
        $this->assertSame(['2', '3'], array_map(static fn (ContentBlockInstance $block) => $block->data['html'], $children[1]));
    }

    public function testContainerInsideSlotIsFlattenedIntoThatSlotAndReported(): void
    {
        $inner = new ContentBlockInstance(self::UUID, 'text', ['html' => 'inner']);
        $nestedColumns = new ContentBlockInstance(self::UUID, 'columns', ['layout' => '1/2-1/2'], [[$inner], []]);
        $document = $this->createDocument([
            new ContentBlockInstance(self::UUID, 'columns', ['layout' => '1/2-1/2'], [[$nestedColumns], []]),
        ]);

        $result = $this->normalizer->normalize($document);

        $slot = $result->blocks[0]->children[0];
        $this->assertCount(1, $slot);
        $this->assertSame('text', $slot[0]->type);
        $this->assertSame(['columns'], $this->normalizer->getLastReport()->getFlattenedContainerTypes());
    }

    public function testAllowedTypesRestrictBlocksAtAnyLevel(): void
    {
        $document = $this->createDocument([
            new ContentBlockInstance(self::UUID, 'image', []),
            new ContentBlockInstance(self::UUID, 'columns', ['layout' => '1/2-1/2'], [[new ContentBlockInstance(self::UUID, 'image', [])], []]),
        ]);

        $result = $this->normalizer->normalize($document, ['columns']);

        $this->assertCount(1, $result->blocks);
        $this->assertSame([[], []], $result->blocks[0]->children);
        $this->assertSame(['image', 'image'], $this->normalizer->getLastReport()->getDroppedBlockTypes());
    }

    public function testInputDocumentIsNotModified(): void
    {
        $document = $this->createDocument([
            new ContentBlockInstance('', 'text', ['html' => '<script>x</script>', 'foreign' => 1]),
        ]);
        $before = $document->toArray();

        $result = $this->normalizer->normalize($document);

        $this->assertSame($before, $document->toArray());
        $this->assertNotSame($document, $result);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     */
    private function createDocument(array $blocks): ContentDocument
    {
        return new ContentDocument(1, ContentProfileEnum::WEB, $blocks);
    }
}
