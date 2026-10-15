<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Form;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Form\Admin\Type\Transformer\ContentDocumentToJsonTransformer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentNormalizer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class ContentDocumentToJsonTransformerTest extends TestCase
{
    private const string UUID = '4d7f2f2e-5b0c-4a58-9a3c-6a1a5c2e8f10';

    private ContentDocumentFactory $contentDocumentFactory;

    private ContentDocumentToJsonTransformer $transformer;

    #[Override]
    protected function setUp(): void
    {
        $this->contentDocumentFactory = new ContentDocumentFactory();
        $this->transformer = $this->createTransformer(ContentProfileEnum::WEB, ['text']);
    }

    public function testNullIsTransformedToEmptyDocumentOfProfile(): void
    {
        $this->assertSame('{"version":1,"profile":"web","blocks":[]}', $this->transformer->transform(null));
    }

    public function testDocumentIsTransformedToJson(): void
    {
        $document = $this->contentDocumentFactory->createFromArray([
            'blocks' => [['id' => self::UUID, 'type' => 'text', 'data' => ['html' => '<p>a</p>']]],
        ]);

        $json = $this->transformer->transform($document);

        $this->assertSame($document->toArray(), json_decode($json, true));
    }

    public function testEmptyJsonIsReversedToEmptyDocumentOfProfile(): void
    {
        $mailTransformer = $this->createTransformer('mail', null);

        $this->assertSame('mail', $mailTransformer->reverseTransform('')->profile);
        $this->assertSame([], $mailTransformer->reverseTransform(null)->blocks);
    }

    public function testJsonIsReversedToNormalizedDocumentWithoutDisallowedBlocks(): void
    {
        $json = json_encode(['version' => 1, 'profile' => 'web', 'blocks' => [
            ['id' => self::UUID, 'type' => 'text', 'data' => ['html' => '<p onclick="x()">a</p>', 'unknown' => 1]],
            ['id' => self::UUID, 'type' => 'image', 'data' => []],
        ]]);

        $document = $this->transformer->reverseTransform($json);

        $this->assertCount(1, $document->blocks);
        $this->assertSame(['html' => '<p>a</p>'], $document->blocks[0]->data);
    }

    public function testInvalidJsonFailsTransformation(): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform('{not json');
    }

    public function testDocumentOfAnotherProfileFailsTransformation(): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform('{"version":1,"profile":"mail","blocks":[]}');
    }

    public function testUnsupportedVersionFailsTransformation(): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform('{"version":2,"profile":"web","blocks":[]}');
    }

    /**
     * @param string[]|null $allowedTypes
     */
    private function createTransformer(string $profile, ?array $allowedTypes): ContentDocumentToJsonTransformer
    {
        $normalizer = new ContentDocumentNormalizer(
            ContentEditorTestFactory::createRegistry(),
            ContentEditorTestFactory::createSanitizer(),
            new ContentProfileEnum(),
        );

        return new ContentDocumentToJsonTransformer($this->contentDocumentFactory, $normalizer, $profile, $allowedTypes);
    }
}
