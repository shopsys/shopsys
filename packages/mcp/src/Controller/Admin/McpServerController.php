<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\McpBundle\Form\Admin\Mcp\GenerateManualTokenFormType;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenFacade;
use Shopsys\McpBundle\Model\Administrator\McpToken\Grid\McpTokenGridFactory;
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
        protected readonly McpTokenGridFactory $mcpTokenGridFactory,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route(path: '/superadmin/mcp-server/', name: 'admin_superadmin_mcp_token', methods: ['GET'])]
    public function listAction(): Response
    {
        $administrator = $this->getCurrentAdministrator();
        $grid = $this->mcpTokenGridFactory->create($administrator);

        return $this->render('@ShopsysMcp/content/superadmin/mcpServer.html.twig', [
            'bearerTokenEnvVarName' => self::BEARER_TOKEN_ENV_VAR,
            'gridView' => $grid->createView(),
            'mcpServerName' => self::MCP_SERVER_NAME,
            'mcpEndpointUrl' => $this->urlGenerator->generate('_mcp_endpoint', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    #[Route(path: '/superadmin/mcp-server/manual-token/', name: 'admin_superadmin_mcp_manual_token', methods: ['GET', 'POST'], condition: 'request.isXmlHttpRequest()')]
    public function manualTokenAction(Request $request): Response
    {
        $form = $this->createForm(GenerateManualTokenFormType::class, null, [
            'action' => $this->generateUrl('admin_superadmin_mcp_manual_token'),
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->render('@ShopsysMcp/content/superadmin/_manualTokenGenerateForm.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        if (!$form->isValid()) {
            return $this->createInvalidJsonResponse($form);
        }

        $administrator = $this->getCurrentAdministrator();
        $data = $form->getData();
        $issuedToken = $this->administratorMcpTokenFacade->issueManualTokenForAdministrator(
            $administrator,
            $data['label'],
            $data['expiresAt'],
        );
        $mcpEndpointUrl = $this->urlGenerator->generate('_mcp_endpoint', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->addSuccessFlash(
            $this->renderView('@ShopsysMcp/content/superadmin/_generatedTokenFlash.html.twig', [
                'bearerTokenEnvVarName' => self::BEARER_TOKEN_ENV_VAR,
                'mcpServerName' => self::MCP_SERVER_NAME,
                'mcpEndpointUrl' => $mcpEndpointUrl,
                'tokenString' => $issuedToken->getTokenString(),
            ]),
        );

        return $this->json(['result' => 'valid']);
    }

    #[Route(path: '/superadmin/mcp-server/token/revoke/{id}/', name: 'admin_superadmin_mcp_token_revoke')]
    #[CsrfProtection]
    public function revokeTokenAction(int $id): Response
    {
        $administrator = $this->getCurrentAdministrator();
        $administratorMcpToken = $this->administratorMcpTokenFacade->findActiveByIdAndAdministrator($administrator, $id);

        if ($administratorMcpToken === null) {
            $this->addErrorFlash(t('The requested active MCP token was not found.'));

            return $this->redirectToRoute('admin_superadmin_mcp_token');
        }

        $this->administratorMcpTokenFacade->revokeToken($administratorMcpToken);
        $this->addSuccessFlash(t('MCP token "%tokenLabel%" revoked', [
            '%tokenLabel%' => $administratorMcpToken->getLabel(),
        ]));

        return $this->redirectToRoute('admin_superadmin_mcp_token');
    }
}
