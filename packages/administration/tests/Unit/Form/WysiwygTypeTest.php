<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Form;

use Override;
use Shopsys\AdministrationBundle\Form\Admin\Type\Transformer\SanitizedHtmlTransformer;
use Shopsys\AdministrationBundle\Form\Admin\Type\WysiwygType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class WysiwygTypeTest extends TypeTestCase
{
    public function testSubmittedHtmlIsSanitized(): void
    {
        $form = $this->factory->create(WysiwygType::class);
        $form->submit('<p onclick="x">a</p><script>alert(1)</script>');

        $this->assertSame('<p>a</p>', $form->getData());
    }

    public function testSubmittedLinkWithTargetGetsRelNoopener(): void
    {
        $form = $this->factory->create(WysiwygType::class);
        $form->submit('<a href="https://example.com" target="_blank">label</a>');

        $this->assertSame('<a href="https://example.com" target="_blank" rel="noopener">label</a>', $form->getData());
    }

    public function testSubmittedNullStaysNull(): void
    {
        $form = $this->factory->create(WysiwygType::class);
        $form->submit(null);

        $this->assertNull($form->getData());
    }

    public function testVariablesAreExposedToTheView(): void
    {
        $variables = [['label' => 'Order number', 'placeholder' => '{order_number}', 'isRequired' => true]];

        $view = $this->factory->create(WysiwygType::class, null, ['variables' => $variables])->createView();

        $this->assertSame($variables, $view->vars['variables']);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);

        parent::setUp();
    }

    #[Override]
    protected function getExtensions(): array
    {
        $type = new WysiwygType(new SanitizedHtmlTransformer(ContentEditorTestFactory::createSanitizer()));

        return [new PreloadedExtension([$type], [])];
    }
}
