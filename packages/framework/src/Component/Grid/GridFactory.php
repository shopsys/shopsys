<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class GridFactory
{
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly RouterInterface $router,
        protected readonly RouteCsrfProtector $routeCsrfProtector,
        protected readonly Environment $twig,
        protected readonly AccessCheckerInterface $accessChecker,
    ) {
    }

    /**
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\EmptyGridIdException
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(string $gridId, DataSourceInterface $dataSource, string $roleConstant)
    {
        return new Grid(
            $gridId,
            $roleConstant,
            $dataSource,
            $this->requestStack,
            $this->router,
            $this->routeCsrfProtector,
            $this->twig,
            $this->accessChecker,
        );
    }
}
