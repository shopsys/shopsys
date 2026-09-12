<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Form\Admin\Type;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\AdministrationBundle\Form\Admin\Type\ActionBarType;
use Shopsys\FormTypesBundle\ActionBarType as BaseActionBarType;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use stdClass;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tests\AdministrationBundle\Unit\Component\Crud\ReviewCrudControllerRegistryFactory;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class ActionBarTypeTest extends TestCase
{
    use SetTranslatorTrait;

    private const string GENERATED_URL_PREFIX = '/generated/';

    #[Override]
    protected function setUp(): void
    {
        $this->setTranslator();
    }

    #[DataProvider('getCrudActionsLeadingBackToTheList')]
    public function testFormOfCrudActionLeadsBackToTheList(string $crudAction): void
    {
        $form = $this->createActionBarForm($this->createCrudRequest($crudAction));

        $this->assertSame(self::GENERATED_URL_PREFIX . 'admin_crud_review_list', $this->getBackLink($form));
    }

    /**
     * @return iterable<string, array{crudAction: string}>
     */
    public static function getCrudActionsLeadingBackToTheList(): iterable
    {
        yield 'edit' => [
            'crudAction' => ActionType::EDIT->value,
        ];

        yield 'create' => [
            'crudAction' => ActionType::CREATE->value,
        ];

        yield 'custom action' => [
            'crudAction' => 'approve',
        ];
    }

    #[DataProvider('getSaveLabelsOfBuiltInActions')]
    public function testSaveLabelOfBuiltInActionIsDerivedFromTheAction(string $crudAction, string $expectedLabel): void
    {
        $form = $this->createActionBarForm($this->createCrudRequest($crudAction));

        $this->assertSame($expectedLabel, $this->getSaveLabel($form));
    }

    /**
     * @return iterable<string, array{crudAction: string, expectedLabel: string}>
     */
    public static function getSaveLabelsOfBuiltInActions(): iterable
    {
        yield 'create' => [
            'crudAction' => ActionType::CREATE->value,
            'expectedLabel' => 'Create',
        ];

        yield 'edit' => [
            'crudAction' => ActionType::EDIT->value,
            'expectedLabel' => 'Save changes',
        ];
    }

    public function testSaveLabelOfCustomActionIsStillDerivedFromTheEntity(): void
    {
        $request = $this->createCrudRequest('approve');

        $this->assertSame('Create', $this->getSaveLabel($this->createActionBarForm($request)));
        $this->assertSame('Save changes', $this->getSaveLabel($this->createActionBarForm($request, ['entity' => new stdClass()])));
    }

    public function testExplicitSaveLabelIsPreferredToTheAction(): void
    {
        $form = $this->createActionBarForm($this->createCrudRequest(ActionType::EDIT->value), ['save_label' => 'Publish']);

        $this->assertSame('Publish', $this->getSaveLabel($form));
    }

    public function testExplicitBackRouteIsPreferredToTheList(): void
    {
        $form = $this->createActionBarForm($this->createCrudRequest(ActionType::EDIT->value), ['back_route' => 'admin_dashboard']);

        $this->assertSame(self::GENERATED_URL_PREFIX . 'admin_dashboard', $this->getBackLink($form));
    }

    public function testExplicitBackUrlIsPreferredToTheList(): void
    {
        $form = $this->createActionBarForm($this->createCrudRequest(ActionType::EDIT->value), ['back_url' => '/somewhere-else/']);

        $this->assertSame('/somewhere-else/', $this->getBackLink($form));
    }

    public function testFormOnTheListItselfHasNoBackLink(): void
    {
        $form = $this->createActionBarForm($this->createCrudRequest(ActionType::LIST->value));

        $this->assertFalse($form->has('back_link'));
    }

    public function testFormOutsideCrudRouteHasNoBackLink(): void
    {
        $request = new Request();
        $request->attributes->set('_route', 'admin_dashboard');

        $form = $this->createActionBarForm($request);

        $this->assertFalse($form->has('back_link'));
    }

    public function testFormWithoutRequestHasNoBackLink(): void
    {
        $form = $this->createActionBarForm(null);

        $this->assertFalse($form->has('back_link'));
    }

    public function testFormOfCrudControllerWithDisabledListHasNoBackLink(): void
    {
        $controller = new class() extends ReviewCrudController {
            #[Override]
            public function configure(CrudConfig $config): void
            {
                $config->disableAction(ActionType::LIST);
            }
        };

        $form = $this->createActionBarForm($this->createCrudRequest('approve'), [], $controller);

        $this->assertFalse($form->has('back_link'));
    }

    private function createCrudRequest(string $crudAction): Request
    {
        $request = new Request();
        $request->attributes->set('_route', 'admin_crud_review_' . $crudAction);
        $request->attributes->set(CrudRouteProvider::IS_CRUD_CONTROLLER, true);
        $request->attributes->set(CrudRouteProvider::CRUD_ACTION, $crudAction);
        $request->attributes->set(CrudRouteProvider::CRUD_CONTROLLER_CLASS, ReviewCrudController::class);

        return $request;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createActionBarForm(
        ?Request $mainRequest,
        array $options = [],
        ?AbstractCrudController $controller = null,
    ): FormInterface {
        $requestStack = new RequestStack();

        if ($mainRequest !== null) {
            $requestStack->push($mainRequest);
        }

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(static fn (string $routeName): string => self::GENERATED_URL_PREFIX . $routeName);

        $routeAccessChecker = $this->createStub(RouteAccessCheckerInterface::class);
        $routeAccessChecker->method('hasAccess')->willReturn(true);

        $crudRouteProvider = new CrudRouteProvider(ReviewCrudControllerRegistryFactory::create(controller: $controller), new InMemoryCache());
        $actionBarTypeExtension = new ActionBarType($routeAccessChecker, $requestStack, $this->createStub(RouterInterface::class), $crudRouteProvider);

        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new PreloadedExtension([new BaseActionBarType($urlGenerator)], [BaseActionBarType::class => [$actionBarTypeExtension]]))
            ->getFormFactory();

        return $formFactory->create(BaseActionBarType::class, null, $options);
    }

    private function getSaveLabel(FormInterface $form): string
    {
        return $form->get('save')->getConfig()->getOption('label');
    }

    private function getBackLink(FormInterface $form): string
    {
        $this->assertTrue($form->has('back_link'), 'The action bar is expected to contain the back link');

        return $form->get('back_link')->getConfig()->getOption('link');
    }
}
