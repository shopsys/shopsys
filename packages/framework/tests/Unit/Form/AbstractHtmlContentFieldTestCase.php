<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\FrameworkBundle\Component\Html\HtmlContentProcessor;
use Shopsys\FrameworkBundle\Form\HtmlContentTypeExtension;
use Shopsys\FrameworkBundle\Form\Transformers\HtmlContentDataTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\WysiwygCdnDataTransformer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

abstract class AbstractHtmlContentFieldTestCase extends TypeTestCase
{
    protected const string LINK_WITH_TARGET = '<a href="https://example.com" target="_blank">label</a>';

    protected const string PROCESSED_LINK_WITH_TARGET = '<a href="https://example.com" target="_blank" rel="noopener">label</a>';

    /**
     * @return class-string<\Symfony\Component\Form\FormTypeInterface>
     */
    abstract protected function getTestedFormTypeClass(): string;

    /**
     * @return \Symfony\Component\Form\FormTypeInterface[]
     */
    abstract protected function getPreloadedFormTypes(): array;

    public function testSubmittedLinkWithTargetGetsRelNoopener(): void
    {
        $form = $this->factory->create($this->getTestedFormTypeClass());
        $form->submit(static::LINK_WITH_TARGET);

        $this->assertSame(static::PROCESSED_LINK_WITH_TARGET, $form->getData());
    }

    public function testSubmittedLinkWithoutTargetIsLeftUntouched(): void
    {
        $html = '<a href="https://example.com">label</a>';

        $form = $this->factory->create($this->getTestedFormTypeClass());
        $form->submit($html);

        $this->assertSame($html, $form->getData());
    }

    #[Override]
    protected function setUp(): void
    {
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);

        parent::setUp();
    }

    /**
     * @return array<class-string<\Symfony\Component\Form\FormTypeInterface>, \Symfony\Component\Form\FormTypeExtensionInterface[]>
     */
    protected function getPreloadedTypeExtensions(): array
    {
        return [
            TextareaType::class => [
                new HtmlContentTypeExtension(
                    new HtmlContentDataTransformer(new HtmlContentProcessor()),
                ),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension($this->getPreloadedFormTypes(), $this->getPreloadedTypeExtensions()),
        ];
    }

    protected function createWysiwygCdnDataTransformer(): WysiwygCdnDataTransformer
    {
        $cdnFacade = $this->createStub(CdnFacade::class);
        $cdnFacade->method('replaceUrlsByCdnForAssets')->willReturnArgument(0);

        return new WysiwygCdnDataTransformer($cdnFacade);
    }
}
