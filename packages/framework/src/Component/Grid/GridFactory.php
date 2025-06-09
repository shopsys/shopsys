<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class GridFactory
{
    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector
     * @param \Twig\Environment $twig
     * @param \Symfony\Bundle\SecurityBundle\Security $security
     */
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly RouterInterface $router,
        protected readonly RouteCsrfProtector $routeCsrfProtector,
        protected readonly Environment $twig,
        protected readonly Security $security,
    ) {
    }

    /**
     * @param string $gridId
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface $dataSource
     * @param string|null $editRole
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\EmptyGridIdException
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(string $gridId, DataSourceInterface $dataSource, ?string $editRole = null)
    {
        return new Grid(
            $gridId,
            $editRole,
            $dataSource,
            $this->requestStack,
            $this->router,
            $this->routeCsrfProtector,
            $this->twig,
            $this->security,
        );
    }
}
