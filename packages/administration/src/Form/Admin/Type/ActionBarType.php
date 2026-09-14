<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form\Admin\Type;

use InvalidArgumentException;
use Override;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\FormTypesBundle\ActionBarType as BaseActionBarType;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

final class ActionBarType extends AbstractTypeExtension
{
    public function __construct(
        private readonly RouteAccessCheckerInterface $routeAccessChecker,
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
        private readonly CrudRouteProvider $crudRouteProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        return [BaseActionBarType::class];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['entity_name', 'entity_identifier'])
            ->setAllowedTypes('entity_name', ['string', 'null'])
            ->setAllowedTypes('entity_identifier', ['string', 'null'])
            ->setDefault('entity_name', null)
            ->setDefault('entity_identifier', null)
            // forms rendered by a CRUD controller action lead back to its list by default, an explicit back_route or back_url still wins
            ->setDefault('back_route', fn (Options $options): ?string => $options['back_url'] === null ? $this->findCrudListRouteName() : null)
            // the built-in create and edit actions know which label fits, other forms keep deciding by the "entity" option
            ->setDefault('save_label', fn (Options $options): ?string => $this->findCrudSaveLabel());
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['entity_name'] = $options['entity_name'];
        $view->vars['entity_identifier'] = $options['entity_identifier'];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Check if we should disable save button based on permissions
        if ($builder->has('save')) {
            $formConfig = $builder->getFormConfig();
            $formAction = $formConfig->getAction();
            $formMethod = $formConfig->getMethod();
            $submitRouteName = $this->extractRouteFromAction($formAction);

            if ($submitRouteName !== null && !$this->routeAccessChecker->hasAccess($submitRouteName, $formMethod)) {
                $builder->remove('save');
            }
        }
    }

    /**
     * Returns the route name of the list action of the CRUD controller handling the main request,
     * or null when the request is not a CRUD route, is the list itself, or the list action is disabled
     */
    private function findCrudListRouteName(): ?string
    {
        $attributes = $this->requestStack->getMainRequest()?->attributes;
        $crudControllerClass = $attributes?->get(CrudRouteProvider::CRUD_CONTROLLER_CLASS);

        if (!is_string($crudControllerClass) || $attributes->get(CrudRouteProvider::CRUD_ACTION) === ActionType::LIST->value) {
            return null;
        }

        try {
            return $this->crudRouteProvider->getRouteItem($crudControllerClass, ActionType::LIST)->getRouteName();
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Returns the save button label of the built-in create and edit CRUD actions handling the main request,
     * or null when the label has to be resolved from the "entity" option (other CRUD actions, forms outside CRUD)
     */
    private function findCrudSaveLabel(): ?string
    {
        $attributes = $this->requestStack->getMainRequest()?->attributes;

        if ($attributes?->get(CrudRouteProvider::IS_CRUD_CONTROLLER) !== true) {
            return null;
        }

        return match ($attributes->get(CrudRouteProvider::CRUD_ACTION)) {
            ActionType::CREATE->value => t('Create'),
            ActionType::EDIT->value => t('Save changes'),
            default => null,
        };
    }

    /**
     * Extract route name from form action URL
     *
     * This method handles different scenarios:
     * 1. Empty action - form submits to current URL
     * 2. Relative URL (e.g., "/admin/product/edit/123") - match with router
     * 3. Absolute URL - extract path and match with router
     *
     * @param string $formAction The form action URL
     * @return string|null The route name if found
     */
    private function extractRouteFromAction(string $formAction): ?string
    {
        $request = $this->requestStack->getMainRequest();

        // If action is empty, form submits to current URL
        if ($formAction === '') {
            return $request?->attributes->get('_route');
        }

        // Parse the action URL to get the path
        $parsedUrl = parse_url($formAction);
        $path = $parsedUrl['path'] ?? $formAction;

        try {
            // Try to match the path to a route
            $routeInfo = $this->router->match($path);

            return $routeInfo['_route'] ?? null;
        } catch (ResourceNotFoundException) {
            // If no route matches, fall back to current route
            // This handles cases where form action might be a query string or fragment
            return $request?->attributes->get('_route');
        }
    }
}
