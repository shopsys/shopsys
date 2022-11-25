<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\HttpFoundation\Response;

class BreadcrumbController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider
     */
    protected $breadcrumbOverrider;

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(BreadcrumbOverrider $breadcrumbOverrider)
    {
        $this->breadcrumbOverrider = $breadcrumbOverrider;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('@ShopsysFramework/Admin/Inline/Breadcrumb/breadcrumb.html.twig', [
            'breadcrumbOverrider' => $this->breadcrumbOverrider,
        ]);
    }
}
