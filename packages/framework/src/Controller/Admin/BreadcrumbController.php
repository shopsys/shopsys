<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;

class BreadcrumbController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(protected readonly BreadcrumbOverrider $breadcrumbOverrider)
    {
    }

    public function indexAction()
    {
        return $this->render('@ShopsysFramework/Admin/Inline/Breadcrumb/breadcrumb.html.twig', [
            'breadcrumbOverrider' => $this->breadcrumbOverrider,
        ]);
    }
}
