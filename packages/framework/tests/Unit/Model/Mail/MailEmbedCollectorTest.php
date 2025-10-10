<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Mail;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\Exception\NamespaceCacheKeyNotFoundException;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Mail\Email;
use Shopsys\FrameworkBundle\Model\Mail\MailEmbedCollector;
use Shopsys\FrameworkBundle\Model\Mail\MailEmbedData;
use Symfony\Component\Mime\Part\DataPart;

class MailEmbedCollectorTest extends TestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache&\PHPUnit\Framework\MockObject\MockObject
     */
    private InMemoryCache $cache;

    private MailEmbedCollector $collector;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createMock(InMemoryCache::class);
        $this->collector = new MailEmbedCollector($this->cache);
    }

    public function testAddEmbedStartsFromZeroWhenNamespaceMissing(): void
    {
        $embedData = new MailEmbedData('data:image/png;base64,' . base64_encode('x'), 'file.png', 'image/png');

        $this->cache
            ->expects($this->once())
            ->method('getValuesByNamespace')
            ->with('mail_embeds_namespace')
            ->willThrowException(new NamespaceCacheKeyNotFoundException('mail_embeds_namespace'));

        $this->cache
            ->expects($this->once())
            ->method('save')
            ->with('mail_embeds_namespace', $embedData, 'embed0');

        $key = $this->collector->addEmbed($embedData);

        $this->assertSame('embed0', $key);
    }

    public function testAddEmbedAppendsSequentialKey(): void
    {
        $existing = [
            'embed0' => new MailEmbedData('data:image/png;base64,' . base64_encode('a'), 'a.png', 'image/png'),
            'embed1' => new MailEmbedData('data:image/png;base64,' . base64_encode('b'), 'b.png', 'image/png'),
        ];
        $newEmbed = new MailEmbedData('data:image/png;base64,' . base64_encode('c'), 'c.png', 'image/png');

        $this->cache
            ->expects($this->once())
            ->method('getValuesByNamespace')
            ->with('mail_embeds_namespace')
            ->willReturn($existing);

        $this->cache
            ->expects($this->once())
            ->method('save')
            ->with('mail_embeds_namespace', $newEmbed, 'embed2');

        $key = $this->collector->addEmbed($newEmbed);

        $this->assertSame('embed2', $key);
    }

    public function testSetEmbedsToMailReplacesFilenameWithGeneratedContentIdAndAddsPart(): void
    {
        $binary = 'hello-image';
        $dataUri = 'data:image/png;base64,' . base64_encode($binary);
        $fileName = 'qrCode.png';
        $contentType = 'image/png';

        $embed = new MailEmbedData($dataUri, $fileName, $contentType);

        $this->cache
            ->expects($this->once())
            ->method('getValuesByNamespace')
            ->with('mail_embeds_namespace')
            ->willReturn(['embed0' => $embed]);

        /** @var \Shopsys\FrameworkBundle\Model\Mail\Email&\PHPUnit\Framework\MockObject\MockObject $email */
        $email = $this->createMock(Email::class);

        $capturedPart = null;
        $email->expects($this->once())
            ->method('addPart')
            ->with($this->callback(function ($part) use (&$capturedPart): bool {
                $this->assertInstanceOf(DataPart::class, $part);
                $capturedPart = $part;

                return true;
            }));

        $originalBody = '<p>Pay here <img src="cid:' . $fileName . '"></p>';

        $resultBody = $this->collector->setEmbedsToMail($originalBody, $email);

        $this->assertNotNull($capturedPart, 'Part was not passed to Email::addPart');
        $contentId = $capturedPart->getContentId();

        $this->assertNotEmpty($contentId, 'Content-ID should be generated');
        $this->assertStringContainsString('cid:' . $contentId, $resultBody);
        $this->assertStringNotContainsString('cid:' . $fileName, $resultBody, 'Original filename cid should be replaced');
    }

    public function testSetEmbedsToMailWithoutEmbedsReturnsOriginalBody(): void
    {
        $this->cache
            ->expects($this->once())
            ->method('getValuesByNamespace')
            ->with('mail_embeds_namespace')
            ->willThrowException(new NamespaceCacheKeyNotFoundException('mail_embeds_namespace'));

        /** @var \Shopsys\FrameworkBundle\Model\Mail\Email&\PHPUnit\Framework\MockObject\MockObject $email */
        $email = $this->createMock(Email::class);
        $email->expects($this->never())->method('addPart');

        $originalBody = '<p>No embeds here</p>';
        $resultBody = $this->collector->setEmbedsToMail($originalBody, $email);

        $this->assertSame($originalBody, $resultBody);
    }
}
