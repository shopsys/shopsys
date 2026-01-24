<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RequestExtension extends AbstractExtension
{
    public function __construct(protected readonly RequestStack $requestStack)
    {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'getAllRequestParams',
                $this->getAllRequestParams(...),
            ),
            new TwigFunction(
                'getRoute',
                $this->getRoute(...),
            ),
            new TwigFunction(
                'getRouteParams',
                $this->getRouteParams(...),
            ),
        ];
    }

    public function getAllRequestParams(): array
    {
        return array_merge(
            $this->getParamsFromRequest(),
            $this->getRouteParams(),
        );
    }

    public function getRoute(): string
    {
        return $this->requestStack->getMainRequest()->attributes->get('_route');
    }

    protected function getParamsFromRequest(): array
    {
        return $this->requestStack->getMainRequest()->query->all();
    }

    public function getRouteParams(): array
    {
        return $this->requestStack->getMainRequest()->attributes->get('_route_params');
    }

    public function getName(): string
    {
        return 'request_extension';
    }
}
