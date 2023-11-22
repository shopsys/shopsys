<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation;

use JMS\TranslationBundle\Exception\InvalidArgumentException;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Translation\MessageIdNormalizer;

class MessageIdNormalizerTest extends TestCase
{
    /**
     * @return string[][]
     */
    public function normalizeMessageIdProvider(): array
    {
        return [
            ['Příliš žluťoučký kůň úpěl ďábelské ódy.', 'Příliš žluťoučký kůň úpěl ďábelské ódy.'],
            [' foo ', 'foo'],
            ['foo  bar', 'foo bar'],
            ["foo\nbar", 'foo bar'],
            ["\t\tfoo\tbar\t", 'foo bar'],
        ];
    }

    /**
     * @dataProvider normalizeMessageIdProvider
     * @param string $messageId
     * @param string $expectedMesssageId
     */
    public function testNormalizeMessageId(string $messageId, string $expectedMesssageId): void
    {
        $messageIdNormalizer = new MessageIdNormalizer();
        $normalizedMessageId = $messageIdNormalizer->normalizeMessageId($messageId);

        $this->assertSame($expectedMesssageId, $normalizedMessageId);
    }

    public function testGetNormalizedCatalogue(): void
    {
        $messageIdNormalizer = new MessageIdNormalizer();

        $source = new FileSource('filepath', 10, 20);

        $message = new Message("message\t \nid", 'message domain');
        $message->setNew(false);
        $message->setMeaning('meaning');
        $message->setLocaleString('locale string');
        $message->setDesc('desc');
        $message->addSource($source);

        $catalogue = new MessageCatalogue();
        $catalogue->setLocale('catalog locale');
        $catalogue->add($message);

        $normalizedCatalogue = $messageIdNormalizer->getNormalizedCatalogue($catalogue);
        $normalizedMessage = $normalizedCatalogue->get('message id', $message->getDomain());

        $this->assertEquals($catalogue->getLocale(), $normalizedCatalogue->getLocale());
        $this->assertEquals('message id', $normalizedMessage->getId());
        $this->assertEquals($message->getMeaning(), $normalizedMessage->getMeaning());
        $this->assertEquals($message->getDesc(), $normalizedMessage->getDesc());
        $this->assertEquals($message->getDomain(), $normalizedMessage->getDomain());
        $this->assertEquals($message->getLocaleString(), $normalizedMessage->getLocaleString());
        $this->assertEquals($message->getSources(), $normalizedMessage->getSources());
        $this->assertEquals($message->getSourceString(), $normalizedMessage->getSourceString());
    }

    public function testGetNormalizedCatalogueInvalidMessageIdArgumentException(): void
    {
        $messageIdNormalizer = new MessageIdNormalizer();

        $source = new FileSource('filepath', 10, 20);

        $message = new Message("message\t \nid", 'message domain');
        $message->setNew(false);
        $message->setMeaning('meaning');
        $message->setLocaleString('locale string');
        $message->setDesc('desc');
        $message->addSource($source);

        $catalogue = new MessageCatalogue();
        $catalogue->setLocale('catalog locale');
        $catalogue->add($message);

        $normalizedCatalogue = $messageIdNormalizer->getNormalizedCatalogue($catalogue);

        $this->expectException(InvalidArgumentException::class);
        $normalizedCatalogue->get($message->getId(), $message->getDomain());
    }
}
