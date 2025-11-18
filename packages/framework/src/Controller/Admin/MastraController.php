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

    /**
     * Get conversation history for a specific thread from Mastra Memory
     *
     * @param string $threadId Thread ID to fetch messages for
     * @return \Symfony\Component\HttpFoundation\JsonResponse JSON array of messages
     */
    #[Route(path: '/mastra/api/memory/threads/{threadId}/messages')]
    public function getThreadMessages(string $threadId): JsonResponse
    {
        $messages = [];

        try {
            $dbPath = dirname(__DIR__, 5) . '/mastra-service/.mastra/mastra.db';

            if (!file_exists($dbPath)) {
                return new JsonResponse([
                    'messages' => [],
                    'threadId' => $threadId,
                    'error' => 'Mastra database not found',
                ]);
            }

            $pdo = new \PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    role,
                    json_extract(content, '$.content') as content,
                    createdAt
                FROM mastra_messages
                WHERE thread_id = :threadId
                ORDER BY createdAt ASC
            ");

            $stmt->execute(['threadId' => $threadId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $messages[] = [
                    'id' => $row['id'],
                    'role' => $row['role'],
                    'content' => $row['content'] ?? '',
                    'createdAt' => $row['createdAt'],
                ];
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'messages' => [],
                'threadId' => $threadId,
                'error' => $e->getMessage(),
            ]);
        }

        return new JsonResponse([
            'messages' => $messages,
            'threadId' => $threadId,
        ]);
    }
}
