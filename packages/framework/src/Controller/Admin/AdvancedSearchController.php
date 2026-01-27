<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFacadeRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_ADMINISTRATOR)]
class AdvancedSearchController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFacadeRegistry $advancedSearchFacadeRegistry
     */
    public function __construct(
        protected readonly AdvancedSearchFacadeRegistry $advancedSearchFacadeRegistry,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $entityType
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/advanced-search/{entityType}/get-rule-form/', methods: ['post'])]
    #[CanView]
    public function getRuleFormAction(Request $request, string $entityType): Response
    {
        $advancedSearchFacade = $this->advancedSearchFacadeRegistry->get($entityType);

        $ruleForm = $advancedSearchFacade->createRuleForm(
            $request->request->getString('filterName'),
            $request->request->getString('newIndex'),
        );

        return $this->render($advancedSearchFacade->getRuleFormTemplatePath(), [
            'rulesForm' => $ruleForm->createView(),
        ]);
    }
}
