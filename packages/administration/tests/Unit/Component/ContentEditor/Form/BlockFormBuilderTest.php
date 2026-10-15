<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\ContentEditor\Form;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\AdministrationBundle\Component\ContentEditor\Form\BlockFormBuilder;
use Shopsys\AdministrationBundle\Form\Admin\Type\MediaType;
use Shopsys\AdministrationBundle\Form\Admin\Type\ProductIdsType;
use Shopsys\AdministrationBundle\Form\Admin\Type\Transformer\SanitizedHtmlTransformer;
use Shopsys\AdministrationBundle\Form\Admin\Type\WysiwygType;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\CheckboxField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ColorField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\MediaField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\NumberField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ProductsField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\VariableField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\WysiwygField;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\ColorPickerType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\Transformers\ProductsIdsToProductsTransformer;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class BlockFormBuilderTest extends TypeTestCase
{
    private BlockFormBuilder $blockFormBuilder;

    /**
     * @return iterable<string, array{fieldName: string, formType: class-string}>
     */
    public static function getFieldMappingData(): iterable
    {
        yield 'text' => [
            'fieldName' => 'text',
            'formType' => TextType::class,
        ];


        yield 'wysiwyg' => [
            'fieldName' => 'wysiwyg',
            'formType' => WysiwygType::class,
        ];

        yield 'url' => [
            'fieldName' => 'url',
            'formType' => UrlType::class,
        ];

        yield 'number' => [
            'fieldName' => 'number',
            'formType' => IntegerType::class,
        ];

        yield 'checkbox' => [
            'fieldName' => 'checkbox',
            'formType' => CheckboxType::class,
        ];

        yield 'choice' => [
            'fieldName' => 'choice',
            'formType' => ChoiceType::class,
        ];

        yield 'color' => [
            'fieldName' => 'color',
            'formType' => ColorPickerType::class,
        ];

        yield 'products' => [
            'fieldName' => 'products',
            'formType' => ProductIdsType::class,
        ];

        yield 'media' => [
            'fieldName' => 'media',
            'formType' => MediaType::class,
        ];

        yield 'variable' => [
            'fieldName' => 'variable',
            'formType' => ChoiceType::class,
        ];
    }

    /**
     * @param class-string $formType
     */
    #[DataProvider('getFieldMappingData')]
    public function testFieldIsMappedToFormType(string $fieldName, string $formType): void
    {
        $form = $this->blockFormBuilder->createDataForm($this->createBlock(), []);

        $this->assertInstanceOf($formType, $form->get($fieldName)->getConfig()->getType()->getInnerType());
    }

    public function testFieldLabelAndRequirednessAreFormOptions(): void
    {
        $form = $this->blockFormBuilder->createDataForm($this->createBlock(), []);

        $this->assertSame('Products label', $form->get('products')->getConfig()->getOption('label'));
        $this->assertTrue($form->get('products')->getConfig()->getOption('required'));
    }

    public function testProductsAreSortableAndNotInOwnCard(): void
    {
        $form = $this->blockFormBuilder->createDataForm($this->createBlock(), []);

        $this->assertTrue($form->get('products')->getConfig()->getOption('sortable'));
        $this->assertFalse($form->get('products')->getConfig()->getOption('renders_in_own_card'));
    }

    public function testVariablesOfEditorReplaceVariablesOfWysiwygField(): void
    {
        $variables = [['label' => 'Order number', 'placeholder' => '{order_number}', 'isRequired' => true, 'isImage' => false]];

        $form = $this->blockFormBuilder->createDataForm($this->createBlock(), $variables);

        $this->assertSame($variables, $form->get('wysiwyg')->getConfig()->getOption('variables'));
    }

    public function testImageVariablesOfEditorAreChoicesOfVariableField(): void
    {
        $variables = [
            ['label' => 'Order number', 'placeholder' => '{order_number}', 'isRequired' => true, 'isImage' => false],
            ['label' => 'Product image URL', 'placeholder' => '{product_image}', 'isRequired' => false, 'isImage' => true],
        ];

        $form = $this->blockFormBuilder->createDataForm($this->createBlock(), $variables);

        $this->assertSame(['Product image URL' => '{product_image}'], $form->get('variable')->getConfig()->getOption('choices'));
    }

    #[Override]
    protected function setUp(): void
    {
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);

        parent::setUp();

        $translator = $this->createStub(Translator::class);
        $translator->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translator);

        $this->blockFormBuilder = new BlockFormBuilder($this->factory);
    }

    #[Override]
    protected function getExtensions(): array
    {
        $productRepository = $this->createStub(ProductRepository::class);
        $types = [
            new WysiwygType(new SanitizedHtmlTransformer(ContentEditorTestFactory::createSanitizer())),
            new MediaType($this->createStub(MediaFacade::class)),
            new ProductsType($this->createStub(ProductsIdsToProductsTransformer::class)),
            new ProductIdsType($productRepository),
        ];

        return [new PreloadedExtension($types, []), new ValidatorExtension(Validation::createValidator())];
    }

    private function createBlock(): AbstractContentBlock
    {
        return new class() extends AbstractContentBlock {
            #[Override]
            public function getLabel(): string
            {
                return 'All fields';
            }

            #[Override]
            public function getFields(): array
            {
                return [
                    TextField::create('text'),
                    WysiwygField::create('wysiwyg'),
                    UrlField::create('url'),
                    NumberField::create('number'),
                    CheckboxField::create('checkbox'),
                    ChoiceField::create('choice', ['a', 'b']),
                    ColorField::create('color'),
                    ProductsField::create('products')->label('Products label')->required(),
                    MediaField::create('media'),
                    VariableField::create('variable', ['{a}']),
                ];
            }
        };
    }
}
