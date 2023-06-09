<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver;

class BreadcrumbController extends FrontBaseController
{
    private BreadcrumbResolver $breadcrumbResolver;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver $breadcrumbResolver
     */
    public function __construct(
        BreadcrumbResolver $breadcrumbResolver
    ) {
        $this->breadcrumbResolver = $breadcrumbResolver;
    }

    /**
     * @param string $routeName
     * @param array $routeParameters
     */
    public function indexAction($routeName, array $routeParameters = [])
    {
        $breadcrumbItems = $this->breadcrumbResolver->resolveBreadcrumbItems($routeName, $routeParameters);

        return $this->render('Front/Inline/Breadcrumb/breadcrumb.html.twig', [
            'breadcrumbItems' => $breadcrumbItems,
        ]);
    }
}
