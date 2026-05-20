<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\McpBundle\Component\Routing\McpRouteName;
use Shopsys\McpBundle\Form\Admin\Mcp\McpOauthAuthorizationFormType;
use Shopsys\McpBundle\Model\OAuth\McpOAuthAuthorizationCodeFacade;
use Shopsys\McpBundle\Model\OAuth\McpOAuthAuthorizationRequestData;
use Shopsys\McpBundle\Model\OAuth\McpOAuthClientRegistrationFacade;
use Shopsys\McpBundle\Model\OAuth\McpOAuthProtocol;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SuperAdminOnly]
class McpOAuthAuthorizationController extends AdminBaseController
{
    public function __construct(
        protected readonly McpOAuthClientRegistrationFacade $mcpOauthClientRegistrationFacade,
        protected readonly McpOAuthAuthorizationCodeFacade $mcpOauthAuthorizationCodeFacade,
    ) {
    }

    #[Route(path: '/superadmin/mcp-server/authorize/', name: McpRouteName::ADMIN_MCP_OAUTH_AUTHORIZE, methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function authorizeAction(Request $request): Response
    {
        $authorizationRequestData = McpOAuthAuthorizationRequestData::createFromRequest($request);
        $form = $this->createForm(McpOauthAuthorizationFormType::class, $authorizationRequestData);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $invalidAuthorizationRequestResponse = $this->getInvalidAuthorizationRequestResponse($request);

            if ($invalidAuthorizationRequestResponse !== null) {
                return $invalidAuthorizationRequestResponse;
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            throw $this->createAccessDeniedException('Invalid OAuth authorization form.');
        }

        $clientRegistrationData = $this->mcpOauthClientRegistrationFacade->findClientRegistrationByClientIdAndRedirectUri(
            $authorizationRequestData->clientId,
            $authorizationRequestData->redirectUri,
        );

        if ($clientRegistrationData === null) {
            return new Response('The OAuth client or redirect_uri is invalid.', Response::HTTP_BAD_REQUEST);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Shopsys\McpBundle\Model\OAuth\McpOAuthAuthorizationRequestData $authorizationRequestData */
            $authorizationRequestData = $form->getData();

            $denyButton = $form->get('deny');

            if ($denyButton instanceof SubmitButton && $denyButton->isClicked()) {
                return $this->redirectToOauthClient($authorizationRequestData->redirectUri, [
                    'error' => 'access_denied',
                    'state' => $authorizationRequestData->state,
                ]);
            }

            $authorizationCode = $this->mcpOauthAuthorizationCodeFacade->createAuthorizationCode(
                $this->getCurrentAdministrator()->getId(),
                $clientRegistrationData->clientId,
                $authorizationRequestData->redirectUri,
                $authorizationRequestData->codeChallenge,
            );

            return $this->redirectToOauthClient($authorizationRequestData->redirectUri, [
                'code' => $authorizationCode,
                'state' => $authorizationRequestData->state,
            ]);
        }

        return $this->render('@ShopsysMcp/content/superadmin/mcpOauthAuthorize.html.twig', [
            'clientRegistration' => $clientRegistrationData,
            'form' => $form->createView(),
        ]);
    }

    protected function getInvalidAuthorizationRequestResponse(Request $request): ?Response
    {
        if ($request->query->get('response_type') !== McpOAuthProtocol::RESPONSE_TYPE_CODE) {
            return new Response('Unsupported response_type.', Response::HTTP_BAD_REQUEST);
        }

        if ($request->query->get('code_challenge_method') !== McpOAuthProtocol::CODE_CHALLENGE_METHOD_S256) {
            return new Response('A PKCE S256 code_challenge is required.', Response::HTTP_BAD_REQUEST);
        }

        if ($request->query->getString('code_challenge') === '') {
            return new Response('A PKCE S256 code_challenge is required.', Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    /**
     * @param array<string, string|null> $query
     */
    protected function redirectToOauthClient(string $redirectUri, array $query): RedirectResponse
    {
        $filteredQuery = array_filter($query, static fn (?string $value): bool => $value !== null);
        $separator = str_contains($redirectUri, '?') ? '&' : '?';

        return new RedirectResponse($redirectUri . $separator . http_build_query($filteredQuery));
    }
}
