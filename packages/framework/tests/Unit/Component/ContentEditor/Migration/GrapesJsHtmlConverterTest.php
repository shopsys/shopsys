<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Migration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\VideoUrlParser;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Migration\ConversionContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Migration\ConversionResult;
use Shopsys\FrameworkBundle\Component\ContentEditor\Migration\GrapesJsHtmlConverter;
use Shopsys\FrameworkBundle\Component\ContentEditor\Migration\LegacyImageImporter;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class GrapesJsHtmlConverterTest extends TestCase
{
    private const array PRODUCT_IDS_BY_CATNUM = [
        '9177759' => 1,
        '5965879P' => 2,
        '9184449' => 3,
        'PHILIPS-M' => 4,
        '7700768' => 5,
        '5964035' => 6,
        '9176508' => 7,
    ];

    /**
     * @return iterable<string, array{fixture: string, profile: string, expectedStructure: string, expectedNotes: string[]}>
     */
    public static function getFixtureData(): iterable
    {
        yield 'article text' => [
            'fixture' => 'createArticleText.html',
            'profile' => ContentProfileEnum::WEB,
            'expectedStructure' => 'text',
            'expectedNotes' => [],
        ];

        yield 'blog article description with products' => [
            'fixture' => 'createDescription.html',
            'profile' => ContentProfileEnum::WEB,
            'expectedStructure' => 'text, products',
            'expectedNotes' => [],
        ];

        yield 'main blog article description' => [
            'fixture' => 'createMainArticleDescription.html',
            'profile' => ContentProfileEnum::WEB,
            'expectedStructure' => 'text, text, products, text, products',
            'expectedNotes' => [],
        ];

        yield 'press article with every kind of block' => [
            'fixture' => 'createForPressArticleText.html',
            'profile' => ContentProfileEnum::WEB,
            'expectedStructure' => 'text, products, text, products, text, columns-1-2[image; text], text, columns-1-1-1[text; text; text], text,'
                . ' columns-1-1[text; text], text, columns-1-1[text, text, text; text, text, text], text, text, map, text, image, button-link',
            'expectedNotes' => ['1 of 4 catalog numbers of products are unknown and were dropped', '<video> is not supported, only its text was kept'],
        ];

        yield 'mail with a button inside of the text' => [
            'fixture' => 'mail/order_status_1.html',
            'profile' => ContentProfileEnum::MAIL,
            'expectedStructure' => 'mail-text, mail-button',
            'expectedNotes' => [],
        ];

        yield 'mail with an image variable inside of the text' => [
            'fixture' => 'mail/watchdog_mail.html',
            'profile' => ContentProfileEnum::MAIL,
            'expectedStructure' => 'mail-text, mail-image-variable, mail-text',
            'expectedNotes' => [],
        ];

        yield 'mail with an image variable in camel case' => [
            'fixture' => 'mail/product_inquiry_customer.html',
            'profile' => ContentProfileEnum::MAIL,
            'expectedStructure' => 'mail-text, mail-image-variable, mail-text',
            'expectedNotes' => [],
        ];

        yield 'plain mail' => [
            'fixture' => 'mail/registration_confirm.html',
            'profile' => ContentProfileEnum::MAIL,
            'expectedStructure' => 'mail-text',
            'expectedNotes' => [],
        ];
    }

    /**
     * @param string[] $expectedNotes
     */
    #[DataProvider('getFixtureData')]
    public function testFixtureIsConverted(
        string $fixture,
        string $profile,
        string $expectedStructure,
        array $expectedNotes,
    ): void {
        $result = $this->convert((string)file_get_contents(__DIR__ . '/fixtures/' . $fixture), $profile);

        $this->assertSame($expectedStructure, $this->describe($result->document->blocks));
        $this->assertSame($expectedNotes, $result->notes);
    }

    public function testProductCatnumsAreConvertedToIdsInTheirOrder(): void
    {
        $result = $this->convert('<div class="gjs-products" data-products="9177759, 5965879P,7700768"></div>');

        $this->assertSame([1, 2, 5], $result->document->blocks[0]->data['productIds']);
    }

    public function testProductsWithoutAnyKnownProductAreDropped(): void
    {
        $result = $this->convert('<div class="gjs-products" data-products="unknown"></div>');

        $this->assertSame([], $result->document->blocks);
        $this->assertSame(
            ['1 of 1 catalog numbers of products are unknown and were dropped', 'products block without any known product was dropped'],
            $result->notes,
        );
    }

    /**
     * @return iterable<string, array{html: string, expectedStructure: string}>
     */
    public static function getRowData(): iterable
    {
        yield 'one column is lifted' => [
            'html' => '<div class="row"><div class="column"><div class="gjs-text-ckeditor">A</div></div></div>',
            'expectedStructure' => 'text',
        ];

        yield 'two columns' => [
            'html' => '<div class="row"><div class="column"><div class="gjs-text-ckeditor">A</div></div><div class="column"></div></div>',
            'expectedStructure' => 'columns-1-1[text; ]',
        ];

        yield 'three columns' => [
            'html' => '<div class="row"><div class="column"></div><div class="column"></div><div class="column"></div></div>',
            'expectedStructure' => 'columns-1-1-1[; ; ]',
        ];

        yield 'four columns are put one under another' => [
            'html' => '<div class="row">' . str_repeat('<div class="column"><div class="gjs-text-ckeditor">A</div></div>', 4) . '</div>',
            'expectedStructure' => 'text, text, text, text',
        ];

        yield 'nested row stays in the column for the normalizer to flatten' => [
            'html' => '<div class="row"><div class="column"><div class="row"><div class="column"></div><div class="column"></div></div></div><div class="column"></div></div>',
            'expectedStructure' => 'columns-1-1[columns-1-1[; ]; ]',
        ];
    }

    #[DataProvider('getRowData')]
    public function testRowIsConvertedToColumns(string $html, string $expectedStructure): void
    {
        $result = $this->convert($html);

        $this->assertSame($expectedStructure, $this->describe($result->document->blocks));
    }

    public function testRowWithMoreThanThreeColumnsAddsNote(): void
    {
        $result = $this->convert('<div class="row">' . str_repeat('<div class="column"></div>', 4) . '</div>');

        $this->assertSame(['row with 4 columns was converted to a sequence of blocks'], $result->notes);
    }

    public function testTextWithImageFloatedLeftPutsImageFirst(): void
    {
        $result = $this->convert('<div class="gjs-text-with-image gjs-text-with-image-float-left"><img src="/content/images/a.jpg" alt="A"><div class="gjs-text-ckeditor text"><p>T</p></div></div>');

        $this->assertSame('columns-1-2[image; text]', $this->describe($result->document->blocks));
        $this->assertSame(['alt' => 'A', 'position' => 'center', 'link' => '', 'mediaId' => 7], $result->document->blocks[0]->children[0][0]->data);
    }

    public function testTextWithImageFloatedRightPutsImageLast(): void
    {
        $result = $this->convert('<div class="gjs-text-with-image gjs-text-with-image-float-right"><img src="/content/a.jpg"><div class="gjs-text-ckeditor text"><p>T</p></div></div>');

        $this->assertSame('columns-2-1[text; image]', $this->describe($result->document->blocks));
    }

    public function testImageIsImportedToMedia(): void
    {
        $result = $this->convert('<img class="web-custom-image image-position-right" src="https://example.com/content/wysiwyg/a.jpg" alt="Alt">');

        $this->assertSame(
            ['alt' => 'Alt', 'position' => 'right', 'link' => '', 'mediaId' => 7],
            $result->document->blocks[0]->data,
        );
        $this->assertSame([], $result->notes);
    }

    public function testImageThatCannotBeImportedKeepsItsUrl(): void
    {
        $result = $this->convert('<img class="web-custom-image" src="https://cdn.example.com/a.jpg">');

        $this->assertSame('https://cdn.example.com/a.jpg', $result->document->blocks[0]->data['url']);
        $this->assertArrayNotHasKey('mediaId', $result->document->blocks[0]->data);
        $this->assertSame(['image "https://cdn.example.com/a.jpg" is not in the content directory, only its url was kept'], $result->notes);
    }

    public function testImageInLinkBlockHasLink(): void
    {
        $result = $this->convert('<a class="gjs-link-block" href="/target"><img src="/content/a.jpg" class="image-position-left"></a>');

        $this->assertSame('image', $result->document->blocks[0]->type);
        $this->assertSame('/target', $result->document->blocks[0]->data['link']);
        $this->assertSame('left', $result->document->blocks[0]->data['position']);
    }

    public function testButtonKeepsTextUrlTargetAndPositionAndGetsPrimaryVariant(): void
    {
        $result = $this->convert('<a class="gjs-button-link button-link-position-right" href="/go" target="_blank" style="background-color:#112233;border-color:#112233"><div class="gjs-text-ckeditor text"> Go  now </div></a>');

        $this->assertSame(
            ['text' => 'Go now', 'url' => '/go', 'position' => 'right', 'variant' => 'primary', 'openInNewTab' => true],
            $result->document->blocks[0]->data,
        );
        $this->assertSame('button-link', $result->document->blocks[0]->type);
    }

    public function testButtonWithoutTargetIsNotOpenedInNewTab(): void
    {
        $result = $this->convert('<a class="gjs-button-link" href="/go">Go</a>');

        $this->assertFalse($result->document->blocks[0]->data['openInNewTab']);
    }

    public function testTableKeepsVariantAndCells(): void
    {
        $result = $this->convert('<div class="gjs-table-custom"><table class="secondary"><tbody><tr><td><div class="gjs-text-ckeditor text">A</div></td><td>B</td></tr><tr><td>C</td><td>D</td></tr></tbody></table></div>');

        $this->assertSame('table', $result->document->blocks[0]->type);
        $this->assertSame(
            ['rows' => 2, 'columns' => 2, 'variant' => 'secondary', 'cells' => [['<div class="gjs-text-ckeditor text">A</div>', 'B'], ['C', 'D']]],
            $result->document->blocks[0]->data,
        );
    }

    public function testTableLargerThanTheBlockAllowsAddsNote(): void
    {
        $result = $this->convert('<div class="gjs-table-custom"><table>' . str_repeat('<tr><td>A</td></tr>', 21) . '</table></div>');

        $this->assertSame(['table 21x1 was cut to the maximal size 20x8'], $result->notes);
    }

    /**
     * @return iterable<string, array{html: string, expectedType: string, expectedData: array<string, mixed>}>
     */
    public static function getIframeData(): iterable
    {
        yield 'youtube is a video' => [
            'html' => '<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>',
            'expectedType' => 'video',
            'expectedData' => ['url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ'],
        ];

        yield 'vimeo is a video' => [
            'html' => '<iframe src="https://player.vimeo.com/video/12345"></iframe>',
            'expectedType' => 'video',
            'expectedData' => ['url' => 'https://player.vimeo.com/video/12345'],
        ];

        yield 'google maps is a map' => [
            'html' => '<iframe src="https://www.google.com/maps/embed?pb=x"></iframe>',
            'expectedType' => 'map',
            'expectedData' => ['url' => 'https://www.google.com/maps/embed?pb=x'],
        ];

        yield 'other iframe keeps its height' => [
            'html' => '<iframe src="https://example.com/widget" style="width: 100%; height: 250px"></iframe>',
            'expectedType' => 'iframe',
            'expectedData' => ['url' => 'https://example.com/widget', 'height' => 250],
        ];

        yield 'other iframe without a height gets the default' => [
            'html' => '<iframe src="https://example.com/widget"></iframe>',
            'expectedType' => 'iframe',
            'expectedData' => ['url' => 'https://example.com/widget', 'height' => 400],
        ];
    }

    /**
     * @param array<string, mixed> $expectedData
     */
    #[DataProvider('getIframeData')]
    public function testIframeIsConverted(string $html, string $expectedType, array $expectedData): void
    {
        $result = $this->convert($html);

        $this->assertSame($expectedType, $result->document->blocks[0]->type);
        $this->assertSame($expectedData, $result->document->blocks[0]->data);
    }

    public function testUnsupportedMarkupIsKeptAsTextWithNote(): void
    {
        $result = $this->convert('<section class="promo"><p>Hello</p></section>');

        $this->assertSame('text', $result->document->blocks[0]->type);
        $this->assertSame('<p>Hello</p>', $result->document->blocks[0]->data['html']);
        $this->assertSame(['<section.promo> is not supported, only its text was kept'], $result->notes);
    }

    public function testNeighbouringParagraphsOutsideOfTextContainerAreOneText(): void
    {
        $result = $this->convert('<h1>Title</h1> <p>A</p> <p>B</p><div class="gjs-products" data-products="9177759"></div><p>C</p>');

        $this->assertSame('text, products, text', $this->describe($result->document->blocks));
        $this->assertSame('<h2>Title</h2> <p>A</p> <p>B</p>', $result->document->blocks[0]->data['html']);
        $this->assertSame([], $result->notes);
    }

    public function testStrayTextBecomesTextBlock(): void
    {
        $result = $this->convert('Just text');

        $this->assertSame('text', $result->document->blocks[0]->type);
        $this->assertSame('Just text', $result->document->blocks[0]->data['html']);
    }

    public function testWebHeadingOneBecomesHeadingTwo(): void
    {
        $result = $this->convert('<div class="gjs-text-ckeditor"><h1>Title</h1></div>');

        $this->assertSame('<h2>Title</h2>', $result->document->blocks[0]->data['html']);
    }

    public function testMailImageIsImportedToMedia(): void
    {
        $result = $this->convert('<img class="mail-custom-image image-position-left" src="/content/wysiwyg/a.jpg" path="/content/wysiwyg/a.jpg" alt="A">', ContentProfileEnum::MAIL);

        $this->assertSame('mail-image', $result->document->blocks[0]->type);
        $this->assertSame(['alt' => 'A', 'position' => 'left', 'link' => '', 'mediaId' => 7], $result->document->blocks[0]->data);
    }

    public function testMailImageVariableIsRecognizedByPath(): void
    {
        $result = $this->convert('<img data-gjs-type="mail-custom-image-with-variable" alt="Name" src="" path="{product_image}">', ContentProfileEnum::MAIL);

        $this->assertSame('mail-image-variable', $result->document->blocks[0]->type);
        $this->assertSame(['variable' => '{product_image}', 'alt' => 'Name'], $result->document->blocks[0]->data);
    }

    public function testMailButtonTakesPositionAndColorFromAttributes(): void
    {
        $result = $this->convert('<a data-gjs-type="button-link" data-link-position="left" href="{url}" style="margin:0;background-color:#ABCDEF;color:#fff">Detail</a>', ContentProfileEnum::MAIL);

        $this->assertSame('mail-button', $result->document->blocks[0]->type);
        $this->assertSame(
            ['text' => 'Detail', 'url' => '{url}', 'position' => 'left', 'color' => '#ABCDEF'],
            $result->document->blocks[0]->data,
        );
    }

    public function testMailTextWithLineBreaksBecomesParagraphs(): void
    {
        $result = $this->convert('<div class="gjs-text-ckeditor">Dear customer,<br/> <br/> thanks<br/>for it<br><br>Best regards</div>', ContentProfileEnum::MAIL);

        $this->assertSame(
            '<p>Dear customer,</p><p>thanks<br />for it</p><p>Best regards</p>',
            $result->document->blocks[0]->data['html'],
        );
    }

    public function testMailTextIsSplitAroundNestedImage(): void
    {
        $result = $this->convert('<div class="gjs-text-ckeditor"><div><p>Before</p><p><img src="/content/a.jpg"></p><p>After</p></div></div>', ContentProfileEnum::MAIL);

        $this->assertSame('mail-text, mail-image, mail-text', $this->describe($result->document->blocks));
    }

    public function testMailTableWithTwoCellsBecomesColumns(): void
    {
        $result = $this->convert('<table><tr><td style="width: 50%"><div class="gjs-text-ckeditor">A</div></td><td style="width: 50%"><a data-gjs-type="button-link" href="/x">B</a></td></tr></table>', ContentProfileEnum::MAIL);

        $this->assertSame('mail-columns[mail-text; mail-button]', $this->describe($result->document->blocks));
    }

    public function testMailTableWithOneCellIsLifted(): void
    {
        $result = $this->convert('<table><tr><td><div class="gjs-text-ckeditor">A</div></td></tr></table>', ContentProfileEnum::MAIL);

        $this->assertSame('mail-text', $this->describe($result->document->blocks));
    }

    public function testMailMarkupOutsideOfTextContainerIsOneMailText(): void
    {
        $result = $this->convert('<h1>Title</h1> <p>Dear {name}</p> {products} <p>Best regards</p>', ContentProfileEnum::MAIL);

        $this->assertSame('mail-text', $this->describe($result->document->blocks));
        $this->assertSame([], $result->notes);
    }

    public function testMailTableWithMoreRowsIsKeptAsText(): void
    {
        $result = $this->convert('<table><tr><td>A</td></tr><tr><td>B</td></tr></table>', ContentProfileEnum::MAIL);

        $this->assertSame('mail-text', $result->document->blocks[0]->type);
        $this->assertSame(['<table> has an unexpected structure and was kept as a text'], $result->notes);
    }

    private function convert(string $html, string $profile = ContentProfileEnum::WEB): ConversionResult
    {
        $productRepository = $this->createStub(ProductRepository::class);
        $productRepository->method('getProductIdsByCatnums')->willReturnCallback(
            static fn (array $catnums): array => array_values(array_filter(array_map(
                static fn (string $catnum): ?int => self::PRODUCT_IDS_BY_CATNUM[$catnum] ?? null,
                $catnums,
            ))),
        );

        $legacyImageImporter = $this->createStub(LegacyImageImporter::class);
        $legacyImageImporter->method('import')->willReturnCallback(
            static fn (string $url): ?int => str_contains($url, '/content/') ? 7 : null,
        );

        $converter = new GrapesJsHtmlConverter(
            $productRepository,
            $legacyImageImporter,
            ContentEditorTestFactory::createSanitizer(),
            new VideoUrlParser(),
            new ContentDocumentFactory(),
        );

        return $converter->convert($html, new ConversionContext($profile, 1, 'en'));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     */
    private function describe(array $blocks): string
    {
        return implode(', ', array_map(
            fn (ContentBlockInstance $block): string => $block->type . ($block->children === null ? '' : '[' . implode('; ', array_map($this->describe(...), $block->children)) . ']'),
            $blocks,
        ));
    }
}
