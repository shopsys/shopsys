<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SuperAdminOnly]
class MastraController extends AdminBaseController
{
    private const string SESSION_THREAD_KEY = 'mastra_thread_id';
    private const string SESSION_SQL_THREAD_KEY = 'mastra_sql_thread_id';

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/mastra/dashboard')]
    public function dashboardAction(Request $request): Response
    {
        $threadId = $this->getOrCreateThreadId($request);
        $resourceId = $this->getResourceId();

        return $this->render('@ShopsysAdministration/content/mastra/index.html.twig', [
            'threadId' => $threadId,
            'resourceId' => $resourceId,
        ]);
    }

    /**
     * Get resource ID representing the current admin user
     *
     * @return string Resource ID for Mastra memory scoping
     */
    protected function getResourceId(): string
    {
        $user = $this->getUser();
        $userId = $user ? $user->getUserIdentifier() : 'anonymous';

        return sprintf('admin_resource_%s', $userId);
    }

    /**
     * Get existing thread ID from session or create new one
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string Thread ID for Mastra conversation persistence
     */
    protected function getOrCreateThreadId(Request $request): string
    {
        $session = $request->getSession();
        $threadId = $session->get(self::SESSION_THREAD_KEY);

        if ($threadId === null) {
            $user = $this->getUser();
            $userId = $user ? $user->getUserIdentifier() : 'anonymous';
            $threadId = sprintf('admin_thread_%s_%s', $userId, bin2hex(random_bytes(8)));
            $session->set(self::SESSION_THREAD_KEY, $threadId);
        }

        return $threadId;
    }

    /**
     * Start new conversation by clearing thread ID from session
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/mastra/dashboard/new-conversation')]
    public function newConversationAction(Request $request): Response
    {
        $request->getSession()->remove(self::SESSION_THREAD_KEY);

        return $this->redirectToRoute('admin_mastra_dashboard');
    }

    /**
     * SQL database query chat interface
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/mastra/sql')]
    public function sqlDashboardAction(Request $request): Response
    {
        $threadId = $this->getOrCreateSqlThreadId($request);
        $resourceId = $this->getResourceId();

        return $this->render('@ShopsysAdministration/content/mastra/sql.html.twig', [
            'threadId' => $threadId,
            'resourceId' => $resourceId,
        ]);
    }

    /**
     * Start new SQL conversation
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/mastra/sql/new-conversation')]
    public function newSqlConversationAction(Request $request): Response
    {
        $request->getSession()->remove(self::SESSION_SQL_THREAD_KEY);

        return $this->redirectToRoute('admin_mastra_sqldashboard');
    }

    /**
     * Get or create thread ID for SQL agent (separate from weather agent)
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    protected function getOrCreateSqlThreadId(Request $request): string
    {
        $session = $request->getSession();
        $threadId = $session->get(self::SESSION_SQL_THREAD_KEY);

        if ($threadId === null) {
            $user = $this->getUser();
            $userId = $user ? $user->getUserIdentifier() : 'anonymous';
            $threadId = sprintf('sql_thread_%s_%s', $userId, bin2hex(random_bytes(8)));
            $session->set(self::SESSION_SQL_THREAD_KEY, $threadId);
        }

        return $threadId;
    }

}
