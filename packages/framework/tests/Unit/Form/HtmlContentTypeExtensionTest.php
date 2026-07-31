<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Html\HtmlContentProcessor;
use Shopsys\FrameworkBundle\Form\HtmlContentTypeExtension;
use Shopsys\FrameworkBundle\Form\Transformers\HtmlContentDataTransformer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

final class HtmlContentTypeExtensionTest extends TypeTestCase
{
    private const string LINK_WITH_TARGET = '<a href="https://example.com" target="_blank">label</a>';

    public function testFieldDeclaringHtmlGetsItsLinksProcessed(): void
    {
        $form = $this->factory->create(TextareaType::class, null, ['contains_html' => true]);
        $form->submit(self::LINK_WITH_TARGET);

        $this->assertSame(
            '<a href="https://example.com" target="_blank" rel="noopener">label</a>',
            $form->getData(),
        );
    }

    /**
     * Plain text fields must not be touched — they should not be passed through a rule about markup at all
     */
    public function testFieldWithoutTheOptionIsLeftUntouched(): void
    {
        $form = $this->factory->create(TextareaType::class);
        $form->submit(self::LINK_WITH_TARGET);

        $this->assertSame(self::LINK_WITH_TARGET, $form->getData());
    }

    public function testFieldOptingOutExplicitlyIsLeftUntouched(): void
    {
        $form = $this->factory->create(TextareaType::class, null, ['contains_html' => false]);
        $form->submit(self::LINK_WITH_TARGET);

        $this->assertSame(self::LINK_WITH_TARGET, $form->getData());
    }

    #[Override]
    protected function setUp(): void
    {
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [],
                [
                    TextareaType::class => [
                        new HtmlContentTypeExtension(
                            new HtmlContentDataTransformer(new HtmlContentProcessor()),
                        ),
                    ],
                ],
            ),
        ];
    }
}
