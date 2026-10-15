<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Document;

use JsonException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception\UnsupportedContentDocumentVersionException;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;

class ContentDocumentFactoryTest extends TestCase
{
    private ContentDocumentFactory $factory;

    #[Override]
    protected function setUp(): void
    {
        $this->factory = new ContentDocumentFactory();
    }

    public function testCreateFromJsonOfToJsonIsIdentical(): void
    {
        $json = '{"version":1,"profile":"mail","blocks":[{"id":"a","type":"text","data":{"html":"<p>Příliš / žluťoučký</p>"}},{"id":"b","type":"columns","data":{"layout":"1/2-1/2"},"children":[[{"id":"c","type":"text","data":{"html":"x"}}],[]]}]}';
        $document = $this->factory->createFromJson($json);

        $result = $this->factory->createFromJson($document->toJson());

        $this->assertEquals($document, $result);
        $this->assertSame($json, $result->toJson());
    }

    public function testCreateEmptyHasNoBlocks(): void
    {
        $document = $this->factory->createEmpty(ContentProfileEnum::MAIL);

        $this->assertSame(1, $document->version);
        $this->assertSame(ContentProfileEnum::MAIL, $document->profile);
        $this->assertTrue($document->isEmpty());
    }

    public function testCreateFromNullOrBlankJsonCreatesEmptyWebDocument(): void
    {
        $this->assertTrue($this->factory->createFromJson(null)->isEmpty());
        $this->assertSame(ContentProfileEnum::WEB, $this->factory->createFromJson(' ')->profile);
    }

    public function testMissingKeysGetDefaults(): void
    {
        $document = $this->factory->createFromArray([
            'blocks' => [['type' => 'text'], 'garbage'],
        ]);

        $this->assertSame(1, $document->version);
        $this->assertSame(ContentProfileEnum::WEB, $document->profile);
        $this->assertEquals([new ContentBlockInstance('', 'text', [], null)], $document->blocks);
    }

    public function testUnknownVersionThrows(): void
    {
        $this->expectException(UnsupportedContentDocumentVersionException::class);

        $this->factory->createFromArray(['version' => 2, 'profile' => 'web', 'blocks' => []]);
    }

    public function testInvalidJsonThrows(): void
    {
        $this->expectException(JsonException::class);

        $this->factory->createFromJson('{not json');
    }
}
