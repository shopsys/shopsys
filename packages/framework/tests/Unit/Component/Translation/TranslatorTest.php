<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Translation\MessageIdNormalizer;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Symfony\Component\Translation\TranslatorBagInterface;

class TranslatorTest extends TestCase
{
    private MockObject|SymfonyTranslator $originalTranslatorMock;

    private Stub|TranslatorBagInterface $originalTranslatorBagStub;

    private MockObject|MessageIdNormalizer $messageIdNormalizerMock;

    private IdentityTranslator $identityTranslator;

    private Translator $translator;

    #[Override]
    protected function setUp(): void
    {
        $this->originalTranslatorMock = $this->createMock(SymfonyTranslator::class);
        $this->originalTranslatorBagStub = $this->createStub(TranslatorBagInterface::class);
        $this->messageIdNormalizerMock = $this->getMockBuilder(MessageIdNormalizer::class)->getMock();
        $this->identityTranslator = new IdentityTranslator();
    }

    private function initTranslator(): void
    {
        $this->translator = new Translator(
            $this->originalTranslatorMock,
            $this->originalTranslatorBagStub,
            $this->identityTranslator,
            $this->messageIdNormalizerMock,
        );
    }

    public function testTransWithNotTranslatedMessageAndSourceLocaleReturnsSourceMessage(): void
    {
        $this->originalTranslatorMock->expects($this->never())->method('trans');

        $this->originalTranslatorBagStub->method('getCatalogue')
            ->willReturn(new MessageCatalogue(Translator::SOURCE_LOCALE, []));

        $this->messageIdNormalizerMock->expects($this->atLeastOnce())->method('normalizeMessageId')
            ->with($this->identicalTo('source message %parameter%'))
            ->willReturn('normalized source message %parameter%');

        $this->initTranslator();

        $translatedMessage = $this->translator->trans(
            'source message %parameter%',
            ['%parameter%' => 'parameter value'],
            null,
            Translator::SOURCE_LOCALE,
        );

        $this->assertSame('normalized source message parameter value', $translatedMessage);
    }

    public function testTransWithTranslatedMessageAndSourceLocaleReturnsTranslatedMessage(): void
    {
        $this->originalTranslatorMock->expects($this->atLeastOnce())->method('trans')
            ->with(
                $this->identicalTo('normalized source message %parameter%'),
                $this->identicalTo(['%parameter%' => 'parameter value']),
            )
            ->willReturn('translated message parameter value');

        $messageCatalogue = new MessageCatalogue(
            Translator::SOURCE_LOCALE,
            [
                'translationDomain' => ['normalized source message %parameter%' => 'translated message %parameter%'],
            ],
        );

        $this->originalTranslatorBagStub->method('getCatalogue')
            ->willReturn($messageCatalogue);

        $this->messageIdNormalizerMock->expects($this->atLeastOnce())->method('normalizeMessageId')
            ->with($this->identicalTo('source message %parameter%'))
            ->willReturn('normalized source message %parameter%');

        $this->initTranslator();

        $translatedMessage = $this->translator->trans(
            'source message %parameter%',
            ['%parameter%' => 'parameter value'],
            'translationDomain',
            Translator::SOURCE_LOCALE,
        );

        $this->assertSame('translated message parameter value', $translatedMessage);
    }

    public function testTransWithSourceLocaleAsDefaultLocaleReturnsSourceMessage(): void
    {
        $this->originalTranslatorMock->expects($this->atLeastOnce())->method('getLocale')
            ->willReturn(Translator::SOURCE_LOCALE);

        $this->originalTranslatorBagStub->method('getCatalogue')
            ->willReturn(new MessageCatalogue(Translator::SOURCE_LOCALE, []));

        $this->messageIdNormalizerMock->expects($this->atLeastOnce())->method('normalizeMessageId')
            ->with($this->identicalTo('source message %parameter%'))
            ->willReturn('normalized source message %parameter%');

        $this->initTranslator();

        $translatedMessage = $this->translator->trans(
            'source message %parameter%',
            ['%parameter%' => 'parameter value'],
        );

        $this->assertSame('normalized source message parameter value', $translatedMessage);
    }

    public function testTransWithNotTranslatedMessageAndNonSourceLocaleReturnsSourceMessage(): void
    {
        $this->originalTranslatorMock->expects($this->atLeastOnce())->method('trans')
            ->with(
                $this->identicalTo('normalized source message %parameter%'),
                $this->identicalTo(['%parameter%' => 'parameter value']),
            )
            ->willReturn('source message parameter value');

        $this->originalTranslatorBagStub->method('getCatalogue')
            ->willReturn(new MessageCatalogue('nonSourceLocale', []));

        $this->messageIdNormalizerMock->expects($this->atLeastOnce())->method('normalizeMessageId')
            ->with($this->identicalTo('source message %parameter%'))
            ->willReturn('normalized source message %parameter%');

        $this->initTranslator();

        $translatedMessage = $this->translator->trans(
            'source message %parameter%',
            ['%parameter%' => 'parameter value'],
            null,
            'nonSourceLocale',
        );

        $this->assertSame('source message parameter value', $translatedMessage);
    }

    public function testTransWithTranslatedMessageAndNonSourceLocaleReturnsTranslatedMessage(): void
    {
        $this->originalTranslatorMock->expects($this->atLeastOnce())->method('trans')
            ->with(
                $this->identicalTo('normalized source message %parameter%'),
                $this->identicalTo(['%parameter%' => 'parameter value']),
            )
            ->willReturn('translated message parameter value');

        $messageCatalogue = new MessageCatalogue(
            'nonSourceLocale',
            [
                'translationDomain' => ['normalized source message %parameter%' => 'translated message %parameter%'],
            ],
        );

        $this->originalTranslatorBagStub->method('getCatalogue')
            ->willReturn($messageCatalogue);

        $this->messageIdNormalizerMock->expects($this->atLeastOnce())->method('normalizeMessageId')
            ->with($this->identicalTo('source message %parameter%'))
            ->willReturn('normalized source message %parameter%');

        $this->initTranslator();

        $translatedMessage = $this->translator->trans(
            'source message %parameter%',
            ['%parameter%' => 'parameter value'],
            'translationDomain',
            'nonSourceLocale',
        );

        $this->assertSame('translated message parameter value', $translatedMessage);
    }

    public function testTransPluralizationWithNotTranslatedMessageAndSourceLocaleReturnsSourceMessage(): void
    {
        $this->originalTranslatorMock->expects($this->never())->method('trans');

        $this->originalTranslatorBagStub->method('getCatalogue')
            ->willReturn(new MessageCatalogue('nonSourceLocale', []));

        $this->messageIdNormalizerMock->expects($this->atLeastOnce())->method('normalizeMessageId')
            ->with($this->identicalTo('{0}zero|{1}source message %parameter%'))
            ->willReturn('{0}zero|{1}normalized source message %parameter%');

        $this->initTranslator();

        $translatedMessage = $this->translator->trans(
            '{0}zero|{1}source message %parameter%',
            [
                '%parameter%' => 'parameter value',
                '%count%' => 1,
            ],
            null,
            Translator::SOURCE_LOCALE,
        );

        $this->assertSame('normalized source message parameter value', $translatedMessage);
    }

    public function testTransPluralizationWithTranslatedMessageAndSourceLocaleReturnsTranslatedMessage(): void
    {
        $this->originalTranslatorMock->expects($this->atLeastOnce())->method('trans')
            ->with(
                $this->identicalTo('{0}zero|{1}normalized source message %parameter%'),
                $this->identicalTo([
                    '%parameter%' => 'parameter value',
                    '%count%' => 1,
                ]),
            )
            ->willReturn('translated message parameter value');

        $messageCatalogue = new MessageCatalogue(
            Translator::SOURCE_LOCALE,
            [
                'translationDomain' => ['{0}zero|{1}normalized source message %parameter%' => '{0}zero|{1}translated message %parameter%'],
            ],
        );

        $this->originalTranslatorBagStub->method('getCatalogue')
            ->willReturn($messageCatalogue);

        $this->messageIdNormalizerMock->expects($this->atLeastOnce())->method('normalizeMessageId')
            ->with($this->identicalTo('{0}zero|{1}source message %parameter%'))
            ->willReturn('{0}zero|{1}normalized source message %parameter%');

        $this->initTranslator();

        $translatedMessage = $this->translator->trans(
            '{0}zero|{1}source message %parameter%',
            [
                '%parameter%' => 'parameter value',
                '%count%' => 1,
            ],
            'translationDomain',
            Translator::SOURCE_LOCALE,
        );

        $this->assertSame('translated message parameter value', $translatedMessage);
    }

    public function testTransPluralizationWithSourceLocaleAsDefaultLocaleReturnsSourceMessage(): void
    {
        $this->originalTranslatorMock->expects($this->atLeastOnce())->method('getLocale')
            ->willReturn(Translator::SOURCE_LOCALE);

        $this->originalTranslatorBagStub->method('getCatalogue')
            ->willReturn(new MessageCatalogue(Translator::SOURCE_LOCALE, []));

        $this->messageIdNormalizerMock->expects($this->atLeastOnce())->method('normalizeMessageId')
            ->with($this->identicalTo('{0}zero|{1}source message %parameter%'))
            ->willReturn('{0}zero|{1}normalized source message %parameter%');

        $this->initTranslator();

        $translatedMessage = $this->translator->trans(
            '{0}zero|{1}source message %parameter%',
            [
                '%parameter%' => 'parameter value',
                '%count%' => 1,
            ],
        );

        $this->assertSame('normalized source message parameter value', $translatedMessage);
    }

    public function testTransPluralizationWithNotTranslatedMessageAndNonSourceLocaleReturnsSourceMessage(): void
    {
        $this->originalTranslatorMock->expects($this->atLeastOnce())->method('trans')
            ->with(
                $this->identicalTo('{0}zero|{1}normalized source message %parameter%'),
                $this->identicalTo([
                    '%parameter%' => 'parameter value',
                    '%count%' => 1,
                ]),
            )
            ->willReturn('source message parameter value');

        $this->originalTranslatorBagStub->method('getCatalogue')
            ->willReturn(new MessageCatalogue('nonSourceLocale', []));

        $this->messageIdNormalizerMock->expects($this->atLeastOnce())->method('normalizeMessageId')
            ->with($this->identicalTo('{0}zero|{1}source message %parameter%'))
            ->willReturn('{0}zero|{1}normalized source message %parameter%');

        $this->initTranslator();

        $translatedMessage = $this->translator->trans(
            '{0}zero|{1}source message %parameter%',
            [
                '%parameter%' => 'parameter value',
                '%count%' => 1,
            ],
            null,
            'nonSourceLocale',
        );

        $this->assertSame('source message parameter value', $translatedMessage);
    }

    public function testTransPluralizationWithTranslatedMessageAndNonSourceLocaleReturnsTranslatedMessage(): void
    {
        $this->originalTranslatorMock->expects($this->atLeastOnce())->method('trans')
            ->with(
                $this->identicalTo('{0}zero|{1}normalized source message %parameter%'),
                $this->identicalTo([
                    '%parameter%' => 'parameter value',
                    '%count%' => 1,
                ]),
            )
            ->willReturn('translated message parameter value');

        $messageCatalogue = new MessageCatalogue(
            'nonSourceLocale',
            [
                'translationDomain' => ['{0}zero|{1}normalized source message %parameter%' => '{0}zero|{1}translated message %parameter%'],
            ],
        );

        $this->originalTranslatorBagStub->method('getCatalogue')
            ->willReturn($messageCatalogue);

        $this->messageIdNormalizerMock->expects($this->atLeastOnce())->method('normalizeMessageId')
            ->with($this->identicalTo('{0}zero|{1}source message %parameter%'))
            ->willReturn('{0}zero|{1}normalized source message %parameter%');

        $this->initTranslator();

        $translatedMessage = $this->translator->trans(
            '{0}zero|{1}source message %parameter%',
            [
                '%parameter%' => 'parameter value',
                '%count%' => 1,
            ],
            'translationDomain',
            'nonSourceLocale',
        );

        $this->assertSame('translated message parameter value', $translatedMessage);
    }
}
