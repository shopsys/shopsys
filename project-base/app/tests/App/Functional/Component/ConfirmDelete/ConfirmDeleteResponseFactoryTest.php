<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\ConfirmDelete;

use Override;
use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Tests\App\Test\FunctionalTestCase;

final class ConfirmDeleteResponseFactoryTest extends FunctionalTestCase
{
    private const string XSS_PAYLOAD = '<img src=x onerror="alert(1)">';
    private const string ESCAPED_XSS_PAYLOAD = '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;';
    private const string DELETE_ROUTE = 'admin_vat_delete';

    /**
     * @inject
     */
    private ConfirmDeleteResponseFactory $confirmDeleteResponseFactory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();
    }

    public function testDirectDeleteEscapesMessageParametersAndKeepsMessageMarkup(): void
    {
        $response = $this->confirmDeleteResponseFactory->createDeleteResponse(
            'Remove "%name%"?<br>It is not used anywhere.',
            self::DELETE_ROUTE,
            1,
            ['%name%' => self::XSS_PAYLOAD],
        );

        $content = (string)$response->getContent();

        $this->assertStringContainsString('Remove "' . self::ESCAPED_XSS_PAYLOAD . '"?<br>It is not used anywhere.', $content);
        $this->assertStringNotContainsString('<img', $content);
    }

    public function testDirectDeleteEscapesListParameterItemsAndJoinsThemWithLineBreaks(): void
    {
        $response = $this->confirmDeleteResponseFactory->createDeleteResponse(
            'Customers:<br>%customersEnumeration%',
            self::DELETE_ROUTE,
            1,
            ['%customersEnumeration%' => ['first@example.com', '"' . self::XSS_PAYLOAD . '"@example.com']],
        );

        $content = (string)$response->getContent();

        $this->assertStringContainsString(
            'Customers:<br>first@example.com<br>&quot;' . self::ESCAPED_XSS_PAYLOAD . '&quot;@example.com',
            $content,
        );
        $this->assertStringNotContainsString('<img', $content);
    }

    public function testDirectDeleteRendersNullParameterAsEmptyText(): void
    {
        $response = $this->confirmDeleteResponseFactory->createDeleteResponse(
            'Article "%name%" set for displaying legal conditions.',
            self::DELETE_ROUTE,
            1,
            ['%name%' => null],
        );

        $this->assertStringContainsString('Article "" set for displaying legal conditions.', (string)$response->getContent());
    }

    public function testSetNewAndDeleteEscapesMessageAndParametersExactlyOnce(): void
    {
        $possibleReplacement = new class() {
            public function getId(): int
            {
                return 2;
            }

            public function getName(): string
            {
                return 'Replacement';
            }
        };

        $response = $this->confirmDeleteResponseFactory->createSetNewAndDeleteResponse(
            'Rate <b>"%name%"</b> is used. Which one you want to set instead?',
            self::DELETE_ROUTE,
            1,
            [$possibleReplacement],
            ['%name%' => self::XSS_PAYLOAD],
        );

        $content = (string)$response->getContent();

        $this->assertStringContainsString(
            'Rate &lt;b&gt;&quot;' . self::ESCAPED_XSS_PAYLOAD . '&quot;&lt;/b&gt; is used. Which one you want to set instead?',
            $content,
        );
        $this->assertStringNotContainsString('&amp;lt;', $content);
        $this->assertStringNotContainsString('<img', $content);
    }
}
