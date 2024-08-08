<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupGridFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerUserRoleGroupController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupGridFactory $gridFactory
     */
    public function __construct(
        protected readonly CustomerUserRoleGroupGridFactory $gridFactory,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/customer/role-group/list/', name: 'admin_superadmin_customer_user_role_group_list')]
    public function listAction(): Response
    {
        $grid = $this->gridFactory->create();

        return $this->render('@ShopsysFramework/Admin/Content/Customer/RoleGroup/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }
}
