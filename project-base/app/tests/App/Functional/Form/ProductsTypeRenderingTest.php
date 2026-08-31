<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form;

use App\DataFixtures\Demo\ProductDataFixture;
use DOMDocument;
use DOMXPath;
use Override;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Tests\App\Test\FunctionalTestCase;
use Twig\Environment;

final class ProductsTypeRenderingTest extends FunctionalTestCase
{
    private const string XPATH_BUTTON = '//button';
    private const string XPATH_INPUT = '//input';
    private const string XPATH_PROTOTYPE_ATTRIBUTE = '//*[@*[contains(name(), "prototype")]]';
    private const string XPATH_PRODUCT_LINK = '//a[contains(@href, "/product/edit/")]';
    private const string XPATH_PRODUCTS_PICKER_HOOK = '//*[contains(concat(" ", normalize-space(@class), " "), " js-products-picker ")]';
    private const string XPATH_DRAG_HANDLE = '//*[contains(concat(" ", normalize-space(@class), " "), " js-products-picker-item-handle ")]';

    /**
     * @inject
     */
    private Environment $twig;

    /**
     * @inject
     */
    private FormFactoryInterface $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    private array $products;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();

        $this->products = [
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2', Product::class),
        ];
    }

    public function testEnabledFieldRendersEditingControls(): void
    {
        $xpath = $this->renderProductsField(false);

        $this->assertCount(1, $xpath->query(self::XPATH_BUTTON), 'Expected the "Add product" button');
        $this->assertCount(
            count($this->products),
            $xpath->query(self::XPATH_INPUT),
            'Expected one submittable input per assigned product',
        );
        $this->assertCount(
            1,
            $xpath->query(self::XPATH_PRODUCTS_PICKER_HOOK),
            'Expected the hook class the products picker JavaScript bootstraps on',
        );
        $this->assertCount(
            count($this->products),
            $xpath->query(self::XPATH_DRAG_HANDLE),
            'Expected a drag handle per assigned product for a sortable field',
        );
        $this->assertGreaterThan(
            0,
            $xpath->query(self::XPATH_PROTOTYPE_ATTRIBUTE)->length,
            'Expected the prototype the JavaScript clones for newly added products',
        );
    }

    public function testDisabledFieldOffersNothingToSubmitOrClick(): void
    {
        $xpath = $this->renderProductsField(true);

        $this->assertCount(0, $xpath->query(self::XPATH_BUTTON), 'A disabled field must offer no action to click');
        $this->assertCount(0, $xpath->query(self::XPATH_INPUT), 'A disabled field must submit nothing');
        $this->assertCount(
            0,
            $xpath->query(self::XPATH_PROTOTYPE_ATTRIBUTE),
            'A disabled field must not carry a prototype for adding new products',
        );
    }

    /**
     * The picker and its drag-and-drop are attached by JavaScript looking up this hook class,
     * so its absence is what actually makes them inert — there is no markup to assert instead.
     */
    public function testDisabledFieldAttachesNoProductsPickerJavaScript(): void
    {
        $xpath = $this->renderProductsField(true);

        $this->assertCount(0, $xpath->query(self::XPATH_PRODUCTS_PICKER_HOOK));
    }

    public function testDisabledFieldRendersNoDragHandle(): void
    {
        $xpath = $this->renderProductsField(true);

        $this->assertCount(
            0,
            $xpath->query(self::XPATH_DRAG_HANDLE),
            'A drag handle suggests an ordering the disabled field cannot change',
        );
    }

    public function testDisabledFieldStillListsAssignedProducts(): void
    {
        $xpath = $this->renderProductsField(true);

        $this->assertCount(
            count($this->products),
            $xpath->query(self::XPATH_PRODUCT_LINK),
            'A read-only field must still show which products are assigned',
        );
    }

    private function renderProductsField(bool $disabled): DOMXPath
    {
        $form = $this->formFactory->createBuilder()
            ->add('products', ProductsType::class, [
                'disabled' => $disabled,
                'sortable' => true,
            ])
            ->getForm();

        $form->get('products')->setData($this->products);

        return $this->createXpath($this->renderForm($form));
    }

    private function renderForm(FormInterface $form): string
    {
        $template = $this->twig->createTemplate('{{ form_widget(form.products) }}');

        return $template->render(['form' => $form->createView()]);
    }

    private function createXpath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        $dom->loadHTML($html, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return new DOMXPath($dom);
    }
}
