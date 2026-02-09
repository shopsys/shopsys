<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

/**
 * Handles access denied exceptions for admin routes
 *
 * When access is denied in admin context:
 * - Adds user-friendly flash message
 * - Redirects to appropriate page (referer or dashboard)
 * - Re-throws exception if not in admin context
 */
final readonly class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ContextResolverInterface $contextResolver,
        private RequestStack $requestStack,
    ) {
    }

    #[Override]
    public function handle(Request $request, AccessDeniedException $accessDeniedException): Response
    {
        if ($this->contextResolver->isCurrentContext(AdminContext::class) === false) {
            // Not in admin context, re-throw exception to let other handlers deal with it
            throw $accessDeniedException;
        }

        $request = $this->requestStack->getCurrentRequest();

        if ($request->isXmlHttpRequest()) {
            return new Response(null, 403);
        }

        if ($request !== null && $request->hasSession()) {
            /** @var \Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface $session */
            $session = $request->getSession();
            $session->getFlashBag()->add(
                FlashMessage::KEY_ERROR,
                t('You are not allowed to access the requested page. Please ask your administrator to grant you access to the requested page.'),
            );
        }

        return new RedirectResponse($this->getRedirectUrl($request), 308);
    }

    /**
     * Determine the best redirect URL after access denial
     *
     * Priority:
     * 1. Valid referer from same host (if different URL or non-GET request)
     * 2. Admin dashboard as fallback
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Current request
     * @return string URL to redirect to
     */
    private function getRedirectUrl(Request $request): string
    {
        // Try to get referer URL
        $referer = $request->headers->get('referer');

        // Use referer if it exists, is from the same host, and different URL
        if ($referer !== null
            && str_starts_with($referer, $request->getSchemeAndHttpHost())
            && $referer !== $request->getUri()
        ) {
            return $referer;
        }

        // Default to admin dashboard
        return $this->urlGenerator->generate('admin_default_dashboard');
    }
}
