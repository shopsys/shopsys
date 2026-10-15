<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\ContentEditor;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentNormalizer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Export\ContentDocumentExporter;
use Shopsys\FrameworkBundle\Component\ContentEditor\Mail\MailContentRenderer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\TransactionFunctionalTestCase;

final class ProjectBlocksTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ContentBlockRegistry $contentBlockRegistry;

    /**
     * @inject
     */
    private ContentDocumentFactory $contentDocumentFactory;

    /**
     * @inject
     */
    private ContentDocumentNormalizer $contentDocumentNormalizer;

    /**
     * @inject
     */
    private ContentDocumentExporter $contentDocumentExporter;

    /**
     * @inject
     */
    private MailContentRenderer $mailContentRenderer;

    public function testProjectBlocksAreOfferedOnlyInTheirProfile(): void
    {
        $webTypes = array_keys($this->contentBlockRegistry->getForProfile(ContentProfileEnum::WEB));
        $mailTypes = array_keys($this->contentBlockRegistry->getForProfile(ContentProfileEnum::MAIL));

        $this->assertContains('faq', $webTypes);
        $this->assertNotContains('mail-promo', $webTypes);
        $this->assertContains('mail-promo', $mailTypes);
        $this->assertNotContains('faq', $mailTypes);
    }

    public function testDocumentWithFaqBlockIsNormalizedAndExported(): void
    {
        $document = $this->contentDocumentFactory->createFromArray([
            'profile' => ContentProfileEnum::WEB,
            'blocks' => [
                ['id' => 'faq-1', 'type' => 'faq', 'data' => [
                    'question' => 'How long is the warranty?',
                    'answer' => '<p>Two <strong>years</strong>.</p>',
                    'unknown' => 'dropped',
                ]],
            ],
        ]);

        $normalizedDocument = $this->contentDocumentNormalizer->normalize($document);

        $this->assertCount(1, $normalizedDocument->blocks);
        $this->assertSame('faq', $normalizedDocument->blocks[0]->type);
        $this->assertArrayNotHasKey('unknown', $normalizedDocument->blocks[0]->data);

        $exportedDocument = $this->contentDocumentExporter->export($normalizedDocument, new ExportContext(Domain::FIRST_DOMAIN_ID, 'en'));

        $this->assertSame('faq', $exportedDocument['blocks'][0]['type']);
        $this->assertSame('How long is the warranty?', $exportedDocument['blocks'][0]['data']['question']);
        $this->assertStringContainsString('<strong>years</strong>', $exportedDocument['blocks'][0]['data']['answer']);
    }

    public function testMailPromoBlockIsRenderedByTheProjectTemplate(): void
    {
        $document = $this->contentDocumentFactory->createFromArray([
            'profile' => ContentProfileEnum::MAIL,
            'blocks' => [
                ['id' => 'promo-1', 'type' => 'mail-promo', 'data' => [
                    'headline' => 'Spring sale',
                    'text' => '<p>Everything <strong>20 %</strong> off</p>',
                    'url' => '{url}',
                ]],
            ],
        ]);

        $html = $this->mailContentRenderer->render($this->contentDocumentNormalizer->normalize($document), Domain::FIRST_DOMAIN_ID);

        $this->assertStringContainsString('bgcolor="#f2f2f2"', $html);
        $this->assertStringContainsString('Spring sale', $html);
        $this->assertStringContainsString('<strong>20 %</strong>', $html);
        $this->assertStringContainsString('href="{url}"', $html);
    }

    public function testMailPromoBlockWithoutUrlHasNoLink(): void
    {
        $document = $this->contentDocumentFactory->createFromArray([
            'profile' => ContentProfileEnum::MAIL,
            'blocks' => [
                ['id' => 'promo-1', 'type' => 'mail-promo', 'data' => ['headline' => 'Spring sale', 'text' => '<p>Sale</p>', 'url' => '']],
            ],
        ]);

        $html = $this->mailContentRenderer->render($document, Domain::FIRST_DOMAIN_ID);

        $this->assertStringContainsString('Spring sale', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }
}
