<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\McpBundle\Component\Routing\McpRouteName;
use Shopsys\McpBundle\Model\ChangeRequest\Exception\McpChangeRequestApprovalFailedException;
use Shopsys\McpBundle\Model\ChangeRequest\Exception\McpChangeRequestNotFoundException;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SuperAdminOnly]
class McpChangeRequestController extends AdminBaseController
{
    protected const int PAGE_SIZE = 20;

    public function __construct(
        protected readonly McpChangeRequestFacade $mcpChangeRequestFacade,
    ) {
    }

    #[Route(path: '/superadmin/mcp-server/change-requests/', name: McpRouteName::ADMIN_MCP_CHANGE_REQUEST_LIST, methods: ['GET'])]
    public function listAction(Request $request): Response
    {
        return $this->render('@ShopsysMcp/content/changeRequest/list.html.twig', [
            'paginationResult' => $this->mcpChangeRequestFacade->getOpenByAdministratorPaginationResult(
                $this->getCurrentAdministrator(),
                $this->getPageFromRequest($request),
                static::PAGE_SIZE,
            ),
        ]);
    }

    #[Route(path: '/superadmin/mcp-server/change-requests/{id}/approve/', name: McpRouteName::ADMIN_MCP_CHANGE_REQUEST_APPROVE, requirements: ['id' => '\d+'])]
    #[CsrfProtection]
    public function approveAction(int $id, Request $request): Response
    {
        try {
            $mcpChangeRequest = $this->mcpChangeRequestFacade->getOpenByIdAndAdministrator($id, $this->getCurrentAdministrator());

            if (!$mcpChangeRequest->isPending()) {
                $this->addErrorFlash(t('The change request failed, update it according to the current data or discard it.'));

                return $this->redirectToList($request);
            }

            if ($this->mcpChangeRequestFacade->approve($mcpChangeRequest)) {
                $this->addSuccessFlash(t('The change was approved and applied.'));
            } else {
                $this->addInfoFlash(t('The change request has already been resolved.'));
            }
        } catch (McpChangeRequestNotFoundException) {
            $this->addInfoFlash(t('The change request has already been resolved.'));
        } catch (McpChangeRequestApprovalFailedException $exception) {
            $this->addErrorFlashTwig(t('The change could not be approved: {{ errorMessage }}'), [
                'errorMessage' => $exception->getMessage(),
            ]);
        }

        return $this->redirectToList($request);
    }

    #[Route(path: '/superadmin/mcp-server/change-requests/{id}/refresh/', name: McpRouteName::ADMIN_MCP_CHANGE_REQUEST_REFRESH, requirements: ['id' => '\d+'])]
    #[CsrfProtection]
    public function refreshAction(int $id, Request $request): Response
    {
        try {
            $mcpChangeRequest = $this->mcpChangeRequestFacade->getOpenByIdAndAdministrator($id, $this->getCurrentAdministrator());

            if (!$mcpChangeRequest->isRefreshable()) {
                return $this->redirectToList($request);
            }

            $this->mcpChangeRequestFacade->refresh($mcpChangeRequest);
            $this->addSuccessFlash(t('The change request was updated according to the current data, check it and approve it again.'));
        } catch (McpChangeRequestNotFoundException) {
            $this->addInfoFlash(t('The change request has already been resolved.'));
        } catch (McpChangeRequestApprovalFailedException $exception) {
            $this->addErrorFlashTwig(t('The change request could not be updated: {{ errorMessage }}'), [
                'errorMessage' => $exception->getMessage(),
            ]);
        }

        return $this->redirectToList($request);
    }

    #[Route(path: '/superadmin/mcp-server/change-requests/{id}/discard/', name: McpRouteName::ADMIN_MCP_CHANGE_REQUEST_DISCARD, requirements: ['id' => '\d+'])]
    #[CsrfProtection]
    public function discardAction(int $id, Request $request): Response
    {
        try {
            $mcpChangeRequest = $this->mcpChangeRequestFacade->getOpenByIdAndAdministrator($id, $this->getCurrentAdministrator());
            $this->mcpChangeRequestFacade->discard($mcpChangeRequest);
            $this->addSuccessFlash(t('The change was discarded.'));
        } catch (McpChangeRequestNotFoundException) {
            $this->addInfoFlash(t('The change request has already been resolved.'));
        }

        return $this->redirectToList($request);
    }

    protected function redirectToList(Request $request): Response
    {
        return $this->redirectToRoute(McpRouteName::ADMIN_MCP_CHANGE_REQUEST_LIST, [
            'page' => $this->getPageFromRequest($request),
        ]);
    }

    protected function getPageFromRequest(Request $request): int
    {
        return $request->query->getInt('page', 1);
    }
}
