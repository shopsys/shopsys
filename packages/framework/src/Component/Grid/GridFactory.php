<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class GridFactory
{
    protected RequestStack $requestStack;

    protected RouterInterface $router;

    protected RouteCsrfProtector $routeCsrfProtector;

    protected Environment $twig;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector
     * @param \Twig\Environment $twig
     */
    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        RouteCsrfProtector $routeCsrfProtector,
        Environment $twig
    ) {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->routeCsrfProtector = $routeCsrfProtector;
        $this->twig = $twig;
    }

    /**
     * @param string $gridId
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface $dataSource
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create($gridId, DataSourceInterface $dataSource)
    {
        return new Grid(
            $gridId,
            $dataSource,
            $this->requestStack,
            $this->router,
            $this->routeCsrfProtector,
            $this->twig
        );
    }
}
