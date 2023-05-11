<?php

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RequestExtension extends AbstractExtension
{
    protected RequestStack $requestStack;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'getAllRequestParams',
                [$this, 'getAllRequestParams']
            ),
            new TwigFunction(
                'getRoute',
                [$this, 'getRoute']
            ),
            new TwigFunction(
                'getRouteParams',
                [$this, 'getRouteParams']
            ),
        ];
    }

    /**
     * @return array
     */
    public function getAllRequestParams()
    {
        return array_merge(
            $this->getParamsFromRequest(),
            $this->getRouteParams()
        );
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->requestStack->getMainRequest()->attributes->get('_route');
    }

    /**
     * @return array
     */
    protected function getParamsFromRequest()
    {
        return $this->requestStack->getMainRequest()->query->all();
    }

    /**
     * @return array
     */
    public function getRouteParams()
    {
        return $this->requestStack->getMainRequest()->attributes->get('_route_params');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'request_extension';
    }
}
