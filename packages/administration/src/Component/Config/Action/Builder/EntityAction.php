<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder;

use InvalidArgumentException;
use ReflectionFunction;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionType;
use Shopsys\AdministrationBundle\Component\Config\PageType;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Webmozart\Assert\Assert;

final class EntityAction extends AbstractActionBuilder
{
    /**
     * @param string $name
     * @param string $label
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\Builder\EntityAction
     */
    public static function create(string $name, string $label): self
    {
        return new self($name, $label, ActionType::ENTITY);
    }

    /**
     * Set function that will determine if action should be displayed
     *
     * @param callable $function
     * @param callable(object $entity): bool|callable(): bool $function Function must return boolean value. If function returns false, action will not be displayed
     * @return $this
     */
    public function displayIf(callable $function): self
    {
        $reflectionFunction = new ReflectionFunction($function);

        Assert::lessThan($reflectionFunction->getNumberOfParameters(), 1, 'Entity action require `displayIf` function with one parameter of entity object');

        $this->displayIf = $function;

        return $this;
    }

    /**
     * Can be used to generate link to another route in the application.
     * Parameters can be passed as array or callable function that will return array.
     * Closure provides entity object as parameter.
     *
     * @param string $route
     * @param array|callable $parameters
     * @param array|callable(object $entity): array $parameters
     * @return $this
     */
    public function linkToRoute(string $route, array|callable $parameters = []): self
    {
        if (is_callable($parameters)) {
            $reflectionFunction = new ReflectionFunction($parameters);

            Assert::lessThan($reflectionFunction->getNumberOfParameters(), 1, 'Entity actions require `parameters` function with one parameter of entity object');
        }

        $this->setRouteParameters($route, $parameters);

        return $this;
    }

    /**
     * Can be used to generate link as URL. That can be used if you want to link to external URL.
     * URL can be provided as string or callable function that will return string.
     * Closure provides entity object as parameter.
     *
     * @param callable|string $url
     * @param string|callable(object $entity): string $url
     * @return $this
     */
    public function linkToUrl(callable|string $url): self
    {
        if (is_callable($url)) {
            $reflectionFunction = new ReflectionFunction($url);

            Assert::lessThan($reflectionFunction->getNumberOfParameters(), 1, 'Entity actions require `parameters` function with one parameter of entity object');
        }

        $this->setLinkParameters($url);

        return $this;
    }

    /**
     * Can be used to generate link to another CRUD controller. This will generate link to the CRUD controller with provided page type.
     * If you are linking to page type that requires entity ID, you must provide callable function that will return entity ID.
     *
     * @param string $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @param callable|null $id
     * @param null|callable(object $entity): int $id
     * @return $this
     */
    public function linkToCrud(string $crudController, PageType $pageType, ?callable $id = null): self
    {
        $routeConfig = CrudRouteProvider::DEFAULT_ROUTES_CONFIG[$pageType->value];

        if ($routeConfig['entityId'] === true && $id === null) {
            throw new InvalidArgumentException('Page type ' . $pageType->value . ' requires entity ID to be provided');
        }

        $this->setCrudParameters($crudController, $pageType, $id);

        return $this;
    }
}
