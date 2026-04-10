<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\McpBundle\Form\Admin\Mcp\GenerateMcpServerTokenFormType;
use Shopsys\McpBundle\Form\Admin\Mcp\RevokeMcpServerTokenFormType;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenFacade;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[SuperAdminOnly]
class McpServerController extends AdminBaseController
{
    protected const string MCP_SERVER_NAME = 'shopsys-mcp';
    protected const string BEARER_TOKEN_ENV_VAR = 'SHOPSYS_MCP_BEARER_TOKEN';

    public function __construct(
        protected readonly AdministratorMcpTokenFacade $administratorMcpTokenFacade,
        protected readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route(path: '/superadmin/mcp-server/', name: 'admin_superadmin_mcp_token', methods: [Request::METHOD_GET])]
    public function indexAction(): Response
    {
        $administrator = $this->getCurrentAdministrator();
        $administratorMcpToken = $this->administratorMcpTokenFacade->findActiveByAdministrator($administrator);

        return $this->render('@ShopsysMcp/content/superadmin/mcpServer.html.twig', [
            'administratorMcpToken' => $administratorMcpToken,
            'bearerTokenEnvVarName' => self::BEARER_TOKEN_ENV_VAR,
            'mcpServerName' => self::MCP_SERVER_NAME,
            'generateForm' => $this->createGenerateForm($administratorMcpToken !== null)->createView(),
            'revokeForm' => $administratorMcpToken !== null ? $this->createRevokeForm()->createView() : null,
            'mcpEndpointUrl' => $this->urlGenerator->generate('_mcp_endpoint', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    #[Route(path: '/superadmin/mcp-server/generate/', name: 'admin_superadmin_mcp_token_generate', methods: [Request::METHOD_POST])]
    public function generateAction(Request $request): Response
    {
        $administrator = $this->getCurrentAdministrator();
        $form = $this->createGenerateForm(
            $this->administratorMcpTokenFacade->findActiveByAdministrator($administrator) !== null,
        );
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));

            return $this->redirectToRoute('admin_superadmin_mcp_token');
        }

        $tokenString = $this->administratorMcpTokenFacade->generateTokenForAdministrator($administrator);
        $mcpEndpointUrl = $this->urlGenerator->generate('_mcp_endpoint', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->addSuccessFlash(
            $this->renderView('@ShopsysMcp/content/superadmin/_generatedTokenFlash.html.twig', [
                'bearerTokenEnvVarName' => self::BEARER_TOKEN_ENV_VAR,
                'mcpServerName' => self::MCP_SERVER_NAME,
                'mcpEndpointUrl' => $mcpEndpointUrl,
                'tokenString' => $tokenString,
            ]),
        );

        return $this->redirectToRoute('admin_superadmin_mcp_token');
    }

    #[Route(path: '/superadmin/mcp-server/revoke/', name: 'admin_superadmin_mcp_token_revoke', methods: [Request::METHOD_POST])]
    public function revokeAction(Request $request): Response
    {
        $administrator = $this->getCurrentAdministrator();
        $form = $this->createRevokeForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));

            return $this->redirectToRoute('admin_superadmin_mcp_token');
        }

        if ($this->administratorMcpTokenFacade->findActiveByAdministrator($administrator) === null) {
            $this->addErrorFlash(t('There is no active MCP token to revoke.'));

            return $this->redirectToRoute('admin_superadmin_mcp_token');
        }

        $this->administratorMcpTokenFacade->revokeTokenForAdministrator($administrator);
        $this->addSuccessFlash(t('MCP token revoked'));

        return $this->redirectToRoute('admin_superadmin_mcp_token');
    }

    protected function createGenerateForm(bool $hasActiveToken): FormInterface
    {
        return $this->createForm(GenerateMcpServerTokenFormType::class, null, [
            'action' => $this->generateUrl('admin_superadmin_mcp_token_generate'),
            'has_active_token' => $hasActiveToken,
        ]);
    }

    protected function createRevokeForm(): FormInterface
    {
        return $this->createForm(RevokeMcpServerTokenFormType::class, null, [
            'action' => $this->generateUrl('admin_superadmin_mcp_token_revoke'),
        ]);
    }
}
