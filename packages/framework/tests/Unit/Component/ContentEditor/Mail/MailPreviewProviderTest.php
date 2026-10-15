<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Mail;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailTextBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\ContentEditor\Mail\MailContentRenderer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Mail\MailPreviewProvider;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class MailPreviewProviderTest extends TestCase
{
    public function testPreviewIsWrappedInMailTemplateAndPlaceholdersAreReplacedWithLabels(): void
    {
        $translator = $this->createStub(Translator::class);
        $translator->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translator);

        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__ . '/../../../../../src/Resources/views', 'ShopsysFramework');
        $renderer = new MailContentRenderer(new ContentBlockRegistry([new MailTextBlock()], []), new Environment($loader));

        $mailTemplateBuilder = $this->createStub(MailTemplateBuilder::class);
        $mailTemplateBuilder->method('getMailTemplateWithContent')->willReturnCallback(
            static fn (int $domainId, string $content): string => '<body>' . $content . '</body>',
        );

        $html = (new MailPreviewProvider($renderer, $mailTemplateBuilder))->getPreviewHtml(
            new ContentDocument(1, 'mail', [new ContentBlockInstance('a', 'mail-text', ['html' => '<p>Order {order_number}</p>'])]),
            1,
            [['label' => 'Order number', 'placeholder' => '{order_number}', 'isRequired' => true, 'isImage' => false]],
        );

        $this->assertStringStartsWith('<body>', $html);
        $this->assertStringContainsString('<p>Order Order number</p>', $html);
        $this->assertStringNotContainsString('{order_number}', $html);
    }
}
