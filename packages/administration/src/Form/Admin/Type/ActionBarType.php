<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form\Admin\Type;

use Override;
use Shopsys\FormTypesBundle\ActionBarType as BaseActionBarType;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

final class ActionBarType extends AbstractTypeExtension
{
    public function __construct(
        private readonly RouteAccessCheckerInterface $routeAccessChecker,
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
    ) {
    }

    #[Override]
    public static function getExtendedTypes(): iterable
    {
        return [BaseActionBarType::class];
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['entity_name', 'entity_identifier'])
            ->setAllowedTypes('entity_name', ['string', 'null'])
            ->setAllowedTypes('entity_identifier', ['string', 'null'])
            ->setDefault('entity_name', null)
            ->setDefault('entity_identifier', null);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['entity_name'] = $options['entity_name'];
        $view->vars['entity_identifier'] = $options['entity_identifier'];
    }

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
