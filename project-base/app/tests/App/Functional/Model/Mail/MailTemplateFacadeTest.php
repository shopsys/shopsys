<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Mail;

use App\Model\Mail\MailTemplateDataFactory;
use App\Model\Mail\MailTemplateFacade;
use App\Model\Mail\MailTemplateRepository;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\TransactionFunctionalTestCase;

final class MailTemplateFacadeTest extends TransactionFunctionalTestCase
{
    private const string TEMPLATE_NAME = 'content_editor_test';

    /**
     * @inject
     */
    private MailTemplateFacade $mailTemplateFacade;

    /**
     * @inject
     */
    private MailTemplateRepository $mailTemplateRepository;

    /**
     * @inject
     */
    private MailTemplateDataFactory $mailTemplateDataFactory;

    /**
     * @inject
     */
    private ContentDocumentFactory $contentDocumentFactory;

    public function testNewTemplateHasEmptyContentThatIsAnEmptyMailDocument(): void
    {
        $this->mailTemplateFacade->createMailTemplateForAllDomains(self::TEMPLATE_NAME);

        $mailTemplate = $this->mailTemplateRepository->getByNameAndDomainId(self::TEMPLATE_NAME, Domain::FIRST_DOMAIN_ID);
        $mailTemplateData = $this->mailTemplateDataFactory->createFromMailTemplate($mailTemplate);

        $this->assertNull($mailTemplate->getContentArray());
        $this->assertSame(ContentProfileEnum::MAIL, $mailTemplateData->content->profile);
        $this->assertTrue($mailTemplateData->content->isEmpty());
    }

    public function testRenderedContentIsTableBasedHtmlWithPlaceholdersKept(): void
    {
        $this->mailTemplateFacade->createMailTemplateForAllDomains(self::TEMPLATE_NAME);
        $mailTemplate = $this->mailTemplateRepository->getByNameAndDomainId(self::TEMPLATE_NAME, Domain::FIRST_DOMAIN_ID);

        $mailTemplateData = $this->mailTemplateDataFactory->createFromMailTemplate($mailTemplate);
        $mailTemplateData->content = $this->contentDocumentFactory->createFromArray([
            'profile' => ContentProfileEnum::MAIL,
            'blocks' => [
                ['id' => 'text-1', 'type' => 'mail-text', 'data' => ['html' => '<p>Your order {order_number}</p>']],
                ['id' => 'button-1', 'type' => 'mail-button', 'data' => ['text' => 'Show order', 'url' => 'https://example.com/order', 'color' => '#00C8B7']],
                ['id' => 'columns-1', 'type' => 'mail-columns', 'data' => [], 'children' => [
                    [['id' => 'text-2', 'type' => 'mail-text', 'data' => ['html' => '<p>Left column</p>']]],
                    [['id' => 'divider-1', 'type' => 'mail-divider', 'data' => []]],
                ]],
            ],
        ]);

        $this->mailTemplateFacade->edit($mailTemplate->getId(), $mailTemplateData);

        $this->assertCount(3, $mailTemplate->getContentArray()['blocks']);

        $renderedTemplate = $this->mailTemplateFacade->getTemplateWithRenderedContent(self::TEMPLATE_NAME, Domain::FIRST_DOMAIN_ID);

        $this->assertStringContainsString('<table', $renderedTemplate->getBody());
        $this->assertStringContainsString('Your order {order_number}', $renderedTemplate->getBody());
        $this->assertStringContainsString('href="https://example.com/order"', $renderedTemplate->getBody());
        $this->assertStringContainsString('Left column', $renderedTemplate->getBody());
        $this->assertStringContainsString('class="mail-column"', $renderedTemplate->getBody());
    }
}
