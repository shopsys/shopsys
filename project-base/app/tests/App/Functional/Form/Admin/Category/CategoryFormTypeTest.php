<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\Category;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Administrator\Administrator;
use App\Model\Category\Category;
use App\Model\Category\CategoryDataFactory;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\FunctionalTestCase;

final class CategoryFormTypeTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private FormFactoryInterface $formFactory;

    /**
     * @inject
     */
    private CategoryDataFactory $categoryDataFactory;

    /**
     * @inject
     */
    private TokenStorageInterface $tokenStorage;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->tokenStorage->setToken(new UsernamePasswordToken($administrator, 'administration', $administrator->getRoles()));
    }

    #[DataProvider('categoryScenarioDataProvider')]
    public function testVisibleParametersCountIsAvailableOnCreateAndEdit(bool $isNew): void
    {
        $category = $isNew ? null : $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $categoryData = $category === null ? $this->categoryDataFactory->create() : $this->categoryDataFactory->createFromCategory($category);
        $form = $this->formFactory->create(CategoryFormType::class, $categoryData, [
            'category' => $category,
            'scenario' => $isNew ? CategoryFormType::SCENARIO_CREATE : CategoryFormType::SCENARIO_EDIT,
            'csrf_protection' => false,
        ]);

        $this->assertSame(5, $form->get('parametersGroup')->get('visibleFilterParametersCount')->getData());
    }

    #[DataProvider('visibleParametersCountDataProvider')]
    public function testVisibleParametersCountValidation(?string $value, bool $isValid, ?int $expectedCount): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $categoryData = $this->categoryDataFactory->createFromCategory($category);
        $form = $this->formFactory->create(CategoryFormType::class, $categoryData, [
            'category' => $category,
            'scenario' => CategoryFormType::SCENARIO_EDIT,
            'csrf_protection' => false,
        ]);

        $form->submit([
            'parametersGroup' => [
                'visibleFilterParametersCount' => $value,
            ],
        ], false);
        $field = $form->get('parametersGroup')->get('visibleFilterParametersCount');

        $this->assertSame($isValid, $field->isValid());

        if ($isValid) {
            $this->assertSame($expectedCount, $categoryData->visibleFilterParametersCount);
        }
    }

    public static function categoryScenarioDataProvider(): iterable
    {
        yield 'create' => [true];

        yield 'edit' => [false];
    }

    public static function visibleParametersCountDataProvider(): iterable
    {
        yield 'zero' => ['0', true, 0];

        yield 'custom' => ['8', true, 8];

        yield 'negative' => ['-1', false, null];

        yield 'blank' => ['', false, null];

        yield 'missing' => [null, false, null];

        yield 'fractional' => ['1.5', false, null];

        yield 'too large' => ['2147483648', false, null];
    }
}
