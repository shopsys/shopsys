<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver;
use Symfony\Component\HttpFoundation\Response;

class BreadcrumbController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver
     */
    private $breadcrumbResolver;

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(string $routeName, array $routeParameters = []): Response
    {
        $breadcrumbItems = $this->breadcrumbResolver->resolveBreadcrumbItems($routeName, $routeParameters);

        return $this->render('Front/Inline/Breadcrumb/breadcrumb.html.twig', [
            'breadcrumbItems' => $breadcrumbItems,
        ]);
    }
}
