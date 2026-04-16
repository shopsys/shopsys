<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\McpBundle\Form\Admin\Mcp\GenerateMcpServerTokenFormType;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken;
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
        protected readonly RouteCsrfProtector $routeCsrfProtector,
        protected readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route(path: '/superadmin/mcp-server/', name: 'admin_superadmin_mcp_token', methods: [Request::METHOD_GET])]
    public function indexAction(): Response
    {
        $administrator = $this->getCurrentAdministrator();
        $manualMcpToken = $this->administratorMcpTokenFacade->findActiveManualTokenByAdministrator($administrator);
        $connectedClientTokens = $this->administratorMcpTokenFacade->findActiveConnectedClientTokensByAdministrator($administrator);

        return $this->render('@ShopsysMcp/content/superadmin/mcpServer.html.twig', [
            'manualMcpToken' => $manualMcpToken,
            'connectedClientTokens' => $connectedClientTokens,
            'bearerTokenEnvVarName' => self::BEARER_TOKEN_ENV_VAR,
            'mcpServerName' => self::MCP_SERVER_NAME,
            'manualTokenGenerateForm' => $this->createManualTokenGenerateForm($manualMcpToken !== null)->createView(),
            'manualTokenRevokeUrl' => $manualMcpToken !== null ? $this->createManualTokenRevokeUrl() : null,
            'connectedClientTokenRevokeUrlsByClientId' => $this->createConnectedClientTokenRevokeUrlsByClientId($connectedClientTokens),
            'mcpEndpointUrl' => $this->urlGenerator->generate('_mcp_endpoint', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    #[Route(path: '/superadmin/mcp-server/manual-token/generate/', name: 'admin_superadmin_mcp_manual_token_generate', methods: [Request::METHOD_POST])]
    public function generateManualTokenAction(Request $request): Response
    {
        $administrator = $this->getCurrentAdministrator();
        $form = $this->createManualTokenGenerateForm(
            $this->administratorMcpTokenFacade->findActiveManualTokenByAdministrator($administrator) !== null,
        );
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));

            return $this->redirectToRoute('admin_superadmin_mcp_token');
        }

        $issuedToken = $this->administratorMcpTokenFacade->issueManualTokenForAdministrator($administrator);
        $mcpEndpointUrl = $this->urlGenerator->generate('_mcp_endpoint', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->addSuccessFlash(
            $this->renderView('@ShopsysMcp/content/superadmin/_generatedTokenFlash.html.twig', [
                'bearerTokenEnvVarName' => self::BEARER_TOKEN_ENV_VAR,
                'mcpServerName' => self::MCP_SERVER_NAME,
                'mcpEndpointUrl' => $mcpEndpointUrl,
                'tokenString' => $issuedToken->getTokenString(),
            ]),
        );

        return $this->redirectToRoute('admin_superadmin_mcp_token');
    }

    #[Route(path: '/superadmin/mcp-server/manual-token/revoke/', name: 'admin_superadmin_mcp_manual_token_revoke')]
    #[CsrfProtection]
    public function revokeManualTokenAction(): Response
    {
        $administrator = $this->getCurrentAdministrator();

        if ($this->administratorMcpTokenFacade->findActiveManualTokenByAdministrator($administrator) === null) {
            $this->addErrorFlash(t('There is no active MCP token to revoke.'));

            return $this->redirectToRoute('admin_superadmin_mcp_token');
        }

        $this->administratorMcpTokenFacade->revokeManualTokenForAdministrator($administrator);
        $this->addSuccessFlash(t('MCP token revoked'));

        return $this->redirectToRoute('admin_superadmin_mcp_token');
    }

    #[Route(path: '/superadmin/mcp-server/connected-client-token/revoke/{clientId}/', name: 'admin_superadmin_mcp_connected_client_token_revoke')]
    #[CsrfProtection]
    public function revokeConnectedClientTokenAction(string $clientId): Response
    {
        $administrator = $this->getCurrentAdministrator();

        if ($clientId === '' || $clientId === AdministratorMcpToken::MANUAL_CLIENT_ID) {
            $this->addErrorFlash(t('The selected MCP session cannot be revoked.'));

            return $this->redirectToRoute('admin_superadmin_mcp_token');
        }

        $connectedClientToken = $this->administratorMcpTokenFacade->findActiveByAdministratorAndClient($administrator, $clientId);

        if ($connectedClientToken === null) {
            $this->addErrorFlash(t('There is no active MCP session to revoke.'));

            return $this->redirectToRoute('admin_superadmin_mcp_token');
        }

        $this->administratorMcpTokenFacade->revokeTokenForAdministratorAndClient($administrator, $clientId);
        $this->addSuccessFlash(t('MCP session revoked'));

        return $this->redirectToRoute('admin_superadmin_mcp_token');
    }

    protected function createManualTokenGenerateForm(bool $hasActiveToken): FormInterface
    {
        return $this->createForm(GenerateMcpServerTokenFormType::class, null, [
            'action' => $this->generateUrl('admin_superadmin_mcp_manual_token_generate'),
            'has_active_token' => $hasActiveToken,
        ]);
    }

    protected function createManualTokenRevokeUrl(): string
    {
        return $this->generateUrl('admin_superadmin_mcp_manual_token_revoke', [
            RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $this->routeCsrfProtector->getCsrfTokenByRoute('admin_superadmin_mcp_manual_token_revoke'),
        ]);
    }

    /**
     * @param array<\Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken> $connectedClientTokens
     * @return array<string, string>
     */
    protected function createConnectedClientTokenRevokeUrlsByClientId(array $connectedClientTokens): array
    {
        $connectedClientTokenRevokeUrlsByClientId = [];

        foreach ($connectedClientTokens as $connectedClientToken) {
            $clientId = $connectedClientToken->getClientId();
            $connectedClientTokenRevokeUrlsByClientId[$clientId] = $this->createConnectedClientTokenRevokeUrl(
                $connectedClientToken,
            );
        }

        return $connectedClientTokenRevokeUrlsByClientId;
    }

    protected function createConnectedClientTokenRevokeUrl(
        AdministratorMcpToken $connectedClientToken,
    ): string {
        return $this->generateUrl('admin_superadmin_mcp_connected_client_token_revoke', [
            'clientId' => $connectedClientToken->getClientId(),
            RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $this->routeCsrfProtector->getCsrfTokenByRoute('admin_superadmin_mcp_connected_client_token_revoke'),
        ]);
    }
}
