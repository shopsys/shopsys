<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Resolver\NotificationBar;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\NotificationBar\NotificationBarsQuery;

final class NotificationBarsQueryTest extends TestCase
{
    #[DataProvider('provideText')]
    public function testPlainTextRemovesHtmlMarkupAfterDecodingEntities(string $html, string $expected): void
    {
        $notificationBar = $this->createStub(NotificationBar::class);
        $notificationBar->method('getText')->willReturn($html);
        $query = new NotificationBarsQuery(
            $this->createStub(NotificationBarFacade::class),
            $this->createStub(Domain::class),
        );

        $this->assertSame($expected, $query->notificationBarPlainTextQuery($notificationBar));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideText(): iterable
    {
        yield 'formatted message' => [' <p>Free <strong>delivery</strong> &amp; returns</p> ', 'Free delivery & returns'];

        yield 'encoded ampersand' => ['Delivery &amp; returns', 'Delivery & returns'];

        yield 'encoded bold markup' => ['&lt;b&gt;Akce&lt;/b&gt;', 'Akce'];

        yield 'encoded tag inside HTML' => ['<p>Use &lt;b&gt; for bold text</p>', 'Use  for bold text'];

        yield 'encoded image tag' => ['&lt;img src=x onerror=alert(1)&gt;', ''];
    }
}
