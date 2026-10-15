<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Mail;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailButtonBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailColumnsBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailDividerBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailImageBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailImageVariableBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailTextBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\ContentEditor\Mail\MailContentRenderer;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class MailContentRendererTest extends TestCase
{
    private MailContentRenderer $renderer;

    public function testTextIsRenderedInTableWithInlineStyleAndKeepsPlaceholders(): void
    {
        $html = $this->render([new ContentBlockInstance('a', 'mail-text', ['html' => '<p>Order <strong>{order_number}</strong></p>'])]);

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('font-family:Arial', $html);
        $this->assertStringContainsString('<p>Order <strong>{order_number}</strong></p>', $html);
    }

    public function testImageIsRenderedWithUrlWidthAndLink(): void
    {
        $html = $this->render([new ContentBlockInstance('a', 'mail-image', [
            'mediaId' => 5,
            'alt' => 'A "cat"',
            'position' => 'right',
            'link' => '{product_url}',
        ])]);

        $this->assertStringContainsString('src="https://example.com/cat.jpg?width=600"', $html);
        $this->assertStringContainsString('width="400"', $html);
        $this->assertStringContainsString('alt="A &quot;cat&quot;"', $html);
        $this->assertStringContainsString('align="right"', $html);
        $this->assertStringContainsString('href="{product_url}"', $html);
    }

    public function testImageWithoutPictureIsSkipped(): void
    {
        $this->assertSame('', trim($this->render([new ContentBlockInstance('a', 'mail-image', ['mediaId' => null])])));
    }

    public function testImageVariableIsRenderedAsPlaceholderInSrc(): void
    {
        $html = $this->render([new ContentBlockInstance('a', 'mail-image-variable', ['variable' => '{product_image}', 'alt' => '{product_name}'])]);

        $this->assertStringContainsString('src="{product_image}"', $html);
        $this->assertStringContainsString('alt="{product_name}"', $html);
    }

    public function testButtonIsRenderedWithLinkTextAndColor(): void
    {
        $html = $this->render([new ContentBlockInstance('a', 'mail-button', [
            'text' => 'Show <order>',
            'url' => 'https://example.com/order?a=1&b=2',
            'position' => 'left',
            'color' => '#112233',
        ])]);

        $this->assertStringContainsString('href="https://example.com/order?a=1&amp;b=2"', $html);
        $this->assertStringContainsString('Show &lt;order&gt;', $html);
        $this->assertStringContainsString('bgcolor="#112233"', $html);
        $this->assertStringContainsString('align="left"', $html);
    }

    public function testColumnsRenderTheirChildrenInTwoCells(): void
    {
        $html = $this->render([new ContentBlockInstance('a', 'mail-columns', ['layout' => '1/2-1/2'], [
            [new ContentBlockInstance('b', 'mail-text', ['html' => '<p>left</p>'])],
            [new ContentBlockInstance('c', 'mail-text', ['html' => '<p>right</p>']), new ContentBlockInstance('d', 'mail-divider', [])],
        ])]);

        $this->assertSame(2, substr_count($html, 'class="mail-column"'));
        $this->assertSame(2, substr_count($html, 'width="50%"'));
        $this->assertMatchesRegularExpression('~mail-column.*<p>left</p>.*mail-column.*<p>right</p>~s', $html);
        $this->assertStringContainsString('border-top:1px solid', $html);
    }

    public function testDividerIsRenderedAsTable(): void
    {
        $html = $this->render([new ContentBlockInstance('a', 'mail-divider', [])]);

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('border-top:1px solid', $html);
    }

    public function testUnknownBlockTypeIsSkipped(): void
    {
        $html = $this->render([
            new ContentBlockInstance('a', 'unknown', ['html' => 'ignored']),
            new ContentBlockInstance('b', 'mail-text', ['html' => '<p>kept</p>']),
        ]);

        $this->assertStringNotContainsString('ignored', $html);
        $this->assertStringContainsString('<p>kept</p>', $html);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $translator = $this->createStub(Translator::class);
        $translator->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translator);

        $media = $this->createStub(Media::class);
        $media->method('getWidth')->willReturn(400);
        $mediaFacade = $this->createStub(MediaFacade::class);
        $mediaFacade->method('getById')->willReturn($media);
        $mediaFacade->method('getImageUrl')->willReturn('https://example.com/cat.jpg?width=600');

        $domainConfig = $this->createStub(DomainConfig::class);
        $domain = $this->createStub(Domain::class);
        $domain->method('getDomainConfigById')->willReturn($domainConfig);

        $registry = new ContentBlockRegistry(
            [new MailTextBlock(), new MailImageBlock($mediaFacade, $domain), new MailImageVariableBlock(), new MailButtonBlock(), new MailColumnsBlock(), new MailDividerBlock()],
            [],
        );

        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__ . '/../../../../../src/Resources/views', 'ShopsysFramework');

        $this->renderer = new MailContentRenderer($registry, new Environment($loader));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     */
    private function render(array $blocks): string
    {
        return $this->renderer->render(new ContentDocument(1, 'mail', $blocks), 1);
    }
}
