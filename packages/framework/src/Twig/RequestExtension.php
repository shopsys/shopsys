<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RequestExtension extends AbstractExtension
{
    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(protected readonly RequestStack $requestStack)
    {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'getAllRequestParams',
                [$this, 'getAllRequestParams'],
            ),
            new TwigFunction(
                'getRoute',
                [$this, 'getRoute'],
            ),
            new TwigFunction(
                'getRouteParams',
                [$this, 'getRouteParams'],
            ),
        ];
    }

    /**
     * @return mixed[]
     */
    public function getAllRequestParams(): array
    {
        return array_merge(
            $this->getParamsFromRequest(),
            $this->getRouteParams(),
        );
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->requestStack->getMainRequest()->attributes->get('_route');
    }

    /**
     * @return mixed[]
     */
    protected function getParamsFromRequest(): array
    {
        return $this->requestStack->getMainRequest()->query->all();
    }

    /**
     * @return mixed[]
     */
    public function getRouteParams(): array
    {
        return $this->requestStack->getMainRequest()->attributes->get('_route_params');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'request_extension';
    }
}
