# Mastra Agentic Workflow in Shopsys Administration — Integration Plan

This plan outlines how to add a Mastra AI agent/workflow into the Shopsys admin (Symfony/Twig) and ship it under our existing Docker stack. It includes architecture theory, phased implementation tasks, and reference snippets following Shopsys package-first principles.

---

## Architecture Overview

### Core Design Principles

- **Package-First Architecture**: All Mastra integration code resides in framework packages (`packages/framework/`, `packages/administration/`), making it available to all Shopsys projects
- **Microservice Pattern**: Run Mastra as a Node sidecar service (HTTP server) alongside PHP app and storefront in Docker Compose
- **Nginx Reverse Proxy**: Expose Mastra over internal network with nginx proxy for same-origin admin API calls (no CORS)
- **Simple Integration**: Admin UI page (Twig + minimal JS) calls Mastra REST endpoints directly
- **Flexible Storage**: Persist memory/workflow state into existing Postgres using @mastra/pg (separate schema), or use file/LibSQL during dev

References: getting-started/installation.mdx, getting-started/studio.mdx, agents/overview.mdx, workflows/overview.mdx, server-db/mastra-client.mdx, server-db/production-server.mdx, reference/cli/mastra.mdx, reference/storage/postgresql.mdx

---

## Implementation Phases

1. Scaffold Mastra service and wire up Docker
2. Add Nginx reverse proxy to Mastra API
3. Use default weather agent for testing
4. Implement admin controller, menu, and template in framework packages
5. Configure storage and secrets
6. Local dev and production deployment
7. Hardening: auth, logging, observability, tests
8. ✅ **Conversation continuity with memory** (COMPLETED)

---

## Phase 1 — Mastra Service Scaffold

### Setup

Location: `./mastra-service` (created by `pnpm create mastra@latest`)

Initialize Mastra service:

```bash
cd mastra-service
pnpm run dev  # exposes http://localhost:4111 (Playground/Swagger)
```

### Package Scripts

Ensure `mastra-service/package.json` includes:

```json
{
  "scripts": {
    "dev": "mastra dev",
    "build": "mastra build",
    "start": "mastra start"
  }
}
```

The scaffold includes:
- Weather tool (`src/mastra/tools/weather.ts`)
- Weather agent (`src/mastra/agents/weather-agent.ts`)
- Weather workflow (`src/mastra/workflows/weather.ts`)

References: getting-started/installation.mdx, reference/cli/mastra.mdx, getting-started/project-structure.mdx

---

## Phase 2 — Docker Compose Service + Nginx Reverse Proxy

### Docker Compose Configuration

Add Mastra service to `docker-compose.yml`:

```yaml
mastra:
  build:
    context: ./mastra-service
    dockerfile: ./Dockerfile
  container_name: shopsys-framework-mastra
  environment:
    OPENAI_API_KEY: ${OPENAI_API_KEY}
    NODE_ENV: development
  volumes:
    - ./mastra-service:/home/node/app
  ports:
    - "4111:4111"  # Playground/Swagger UI
  command: ["sh", "-lc", "corepack enable && pnpm i && pnpm run dev"]
  depends_on:
    - postgres
```

**Note**: `project-base/scripts/configure.sh` generates `docker-compose.yml` from templates. If regenerated, re-apply the mastra service configuration.

### Nginx Reverse Proxy

Add to `docker/nginx/nginx.local.conf`:

**1. Add upstream** (near top of file):

```nginx
upstream mastra-upstream {
    server mastra:4111;
}
```

**2. Add location block** (inside server block listening on port 8080, before the catch-all `location ~ /`):

```nginx
# Mastra API proxy - only proxy API endpoints, not Playground UI
location ~ ^/(?:[^/]+/)?mastra/api/(.*)$ {
    proxy_hide_header Access-Control-Allow-Origin;
    proxy_set_header Host $http_host;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_buffering off;
    proxy_read_timeout 300s;
    proxy_send_timeout 300s;
    rewrite ^/(?:[^/]+/)?mastra/api/(.*)$ /api/$1 break;
    proxy_pass http://mastra-upstream;
}
```

**Key Points**:
- Proxies `/mastra/api/*` → `http://mastra:4111/api/*`
- Strips `/mastra` prefix before forwarding
- Supports WebSocket upgrades (for streaming)
- Long timeouts (300s) for AI operations
- Playground UI remains accessible directly at `http://127.0.0.1:4111`

Admin JavaScript calls Mastra via proxy: `/mastra/api/agents/weatherAgent/generate`

References: server-db/production-server.mdx, server-db/middleware.mdx

---

## Phase 3 — Weather Agent Testing

### Default Agent

The scaffolded Mastra service includes a working weather agent at `mastra-service/src/mastra/agents/weather-agent.ts`.

No code changes needed — just ensure Mastra service runs and exposes the REST API.

### Test Endpoint

```bash
# Start Mastra service
cd mastra-service
pnpm run dev

# Test weather agent (direct)
curl -X POST http://localhost:4111/api/agents/weatherAgent/generate \
  -H "Content-Type: application/json" \
  -d '{"messages":[{"role":"user","content":"What is the weather in London?"}]}'

# Test via nginx proxy (after Phase 2 setup)
curl -X POST http://127.0.0.1:8000/mastra/api/agents/weatherAgent/generate \
  -H "Content-Type: application/json" \
  -d '{"messages":[{"role":"user","content":"What is the weather in London?"}]}'
```

References: agents/overview.mdx, server-db/studio.mdx

---

## Phase 4 — Admin Integration (Package-First)

Following Shopsys package-first architecture, implement all admin UI components in framework packages.

### Controller — Framework Package

**File**: `packages/framework/src/Controller/Admin/MastraController.php`

```php
<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SuperAdminOnly]
class MastraController extends AdminBaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/mastra/dashboard')]
    public function dashboardAction(): Response
    {
        return $this->render('@ShopsysAdministration/content/mastra/index.html.twig');
    }
}
```

**Key Attributes**:
- `#[SuperAdminOnly]` — Class-level security (only super admins)
- Route: `/superadmin/mastra/dashboard` (superadmin prefix for platform features)
- Uses `protected` visibility (framework pattern allows project-base overrides)

### Menu Integration — SideMenuBuilder

**File**: `packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php`

**1. Add constant** (around line 189, with other integration constants):

```php
public const string MASTRA_DASHBOARD = 'mastra_dashboard';
```

**2. Add menu item** in `createIntegrationsMenu()` method (around line 901):

```php
protected function createIntegrationsMenu(): ItemInterface
{
    $integrationsMenu = $this->menuFactory->createItem(static::ROOT_INTEGRATIONS, ['label' => t('Integrations')]);
    $integrationsMenu->setExtra('icon', 'puzzle');

    $integrationsMenu->addChild(static::LIST_FEED, ['route' => 'admin_feed_list', 'label' => t('XML Feeds')]);
    $integrationsMenu->addChild(static::MASTRA_DASHBOARD, ['route' => 'admin_mastra_dashboard', 'label' => t('Mastra Assistant')]);

    $heurekaMenu = $integrationsMenu->addChild(static::SECTION_HEUREKA, ['label' => t('Heureka')]);
    $heurekaMenu->addChild(static::HEUREKA_SETTINGS, ['route' => 'admin_heureka_setting', 'label' => t('Heureka')]);

    $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_INTEGRATIONS, $integrationsMenu);

    return $integrationsMenu;
}
```

Menu appears at: **Integrations → Mastra Assistant**

### Template — Administration Package

**File**: `packages/administration/templates/content/mastra/index.html.twig`

```twig
{% extends '@ShopsysAdministration/layout/layout_with_panel.html.twig' %}

{% block title %}- {{ 'Mastra Assistant'|trans }}{% endblock %}

{% block pre_title %}{{ 'Integrations'|trans }}{% endblock %}
{% block h1 %}{{ 'Mastra Assistant'|trans }}{% endblock %}

{% block main_content %}
    <div class="card card-body mb-3">
        <form id="mastra-form" onsubmit="return false;">
            <div class="mb-3">
                <label for="city-input" class="form-label">{{ 'City'|trans }}</label>
                <input id="city-input"
                       class="form-control"
                       type="text"
                       placeholder="{{ 'Enter city (e.g., London)'|trans }}"
                />
            </div>
            <button id="ask-btn" class="btn btn-primary" type="button">
                {{ 'Ask weather'|trans }}
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ 'Response'|trans }}</h3>
        </div>
        <div class="card-body">
            <pre id="mastra-output" style="white-space: pre-wrap; min-height: 100px;">{{ 'Click "Ask weather" to test the Mastra agent.'|trans }}</pre>
        </div>
    </div>

    <script>
        (function () {
            const btn = document.getElementById('ask-btn');
            const input = document.getElementById('city-input');
            const out = document.getElementById('mastra-output');

            btn.addEventListener('click', async () => {
                const city = input.value || 'London';
                out.textContent = '{{ 'Loading...'|trans }}';

                try {
                    const res = await fetch('/mastra/api/agents/weatherAgent/generate', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            messages: [
                                { role: 'user', content: `What's the weather like in ${city}?` }
                            ]
                        })
                    });

                    if (!res.ok) {
                        const text = await res.text().catch(() => '');
                        throw new Error(`HTTP ${res.status}: ${text}`);
                    }

                    const json = await res.json();
                    out.textContent = json?.text ?? JSON.stringify(json, null, 2);
                } catch (err) {
                    out.textContent = '{{ 'Error'|trans }}: ' + (err?.message || err);
                }
            });
        })();
    </script>
{% endblock %}
```

**Template Features**:
- Tabler card structure (Shopsys admin theme)
- All text internationalized with `trans` filter
- Proper form labels and accessibility
- Error handling in JavaScript
- Calls Mastra via nginx proxy: `/mastra/api/agents/weatherAgent/generate`

### Why Package-First?

This implementation follows Shopsys monorepo best practices:

- **Controller in `packages/framework/`**: Makes Mastra available to all projects using Shopsys Framework
- **Template in `packages/administration/`**: Shared admin UI component
- **Menu in `SideMenuBuilder`**: Direct framework integration (not event subscriber)
- **`protected` visibility**: Allows project-base to extend/override if needed
- **SuperAdmin-only**: Platform-level feature, not project-specific

Projects can customize by:
- Extending `MastraController` in `project-base/src/Controller/Admin/` if needed
- Overriding template in `project-base/templates/`
- Adding project-specific agents in `project-base/src/Model/Mastra/`

References: CLAUDE.md (Monorepo Architecture, Package-First Development)

---

## Phase 5 — Storage and Secrets

### Storage Configuration

**Development**: Use LibSQL (file-based) for quick iteration

**Production**: Use PostgreSQL with separate schema

**File**: `mastra-service/src/mastra/index.ts`

```typescript
import { Mastra } from '@mastra/core';
import { PostgresStore } from '@mastra/pg';

export const mastra = new Mastra({
  storage: new PostgresStore({
    connectionString: process.env.DATABASE_URL || 'postgresql://root:root@postgres:5432/shopsys',
    schemaName: 'mastra',
  }),
  // ... agents, workflows, tools
});
```

The `mastra` schema is created automatically by `@mastra/pg`.

### Secrets Management

**Never commit API keys!**

Add to `.env` (same directory as `docker-compose.yml`):

```env
OPENAI_API_KEY=sk-proj-...your-key-here...
```

The docker-compose service reads `${OPENAI_API_KEY}` from environment.

Restart after changes:

```bash
mutagen-compose down
mutagen-compose up -d
```

References: server-db/storage.mdx, reference/storage/postgresql.mdx

---

## Phase 6 — Local Development and Production

### Local Development

**Start services** (macOS uses `mutagen-compose`):

```bash
# Start all services including Mastra
mutagen-compose up -d postgres php-fpm webserver storefront mastra

# View Mastra logs
mutagen-compose logs -f mastra

# Access points:
# - Mastra Playground: http://127.0.0.1:4111
# - Admin Mastra page: http://127.0.0.1:8000/admin/superadmin/mastra/dashboard
```

**Development workflow**:

1. Edit Mastra agents/tools in `mastra-service/src/mastra/`
2. Changes auto-reload (dev mode with hot reload)
3. Test via Playground UI or admin page
4. No restart needed for code changes

### Production Build

**Dockerfile**: `mastra-service/Dockerfile`

```dockerfile
FROM node:20-alpine AS deps
WORKDIR /home/node/app
COPY package.json pnpm-lock.yaml* ./
RUN corepack enable && pnpm i --frozen-lockfile

FROM node:20-alpine AS build
WORKDIR /home/node/app
COPY --from=deps /home/node/app/node_modules ./node_modules
COPY . .
RUN corepack enable && pnpm run build

FROM node:20-alpine AS runner
WORKDIR /home/node/app
ENV NODE_ENV=production
COPY --from=build /home/node/app/.mastra/output ./.mastra/output
EXPOSE 4111
CMD ["pnpm", "run", "start", "--", "--dir", ".mastra/output"]
```

**Production docker-compose** (update mastra service):

```yaml
mastra:
  build:
    context: ./mastra-service
    dockerfile: ./Dockerfile
  container_name: shopsys-framework-mastra
  environment:
    OPENAI_API_KEY: ${OPENAI_API_KEY}
    NODE_ENV: production
    DATABASE_URL: postgresql://root:root@postgres:5432/shopsys
  depends_on:
    - postgres
```

References: server-db/production-server.mdx, reference/cli/mastra.mdx

---

## Phase 7 — Hardening and Observability

### Authentication

Add middleware in Mastra to validate admin requests:

**File**: `mastra-service/src/mastra/index.ts`

```typescript
import { Mastra } from '@mastra/core';

export const mastra = new Mastra({
  server: {
    middleware: [async (c, next) => {
      // Protect agent/workflow endpoints
      if (c.req.path.startsWith('/agents') || c.req.path.startsWith('/workflows')) {
        const token = c.req.header('X-Admin-Token');
        if (token !== process.env.MASTRA_ADMIN_TOKEN) {
          return new Response('Unauthorized', { status: 401 });
        }
      }
      await next();
    }],
  },
  // ... storage, agents, workflows
});
```

Update admin template to send token:

```javascript
fetch('/mastra/api/agents/weatherAgent/generate', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-Admin-Token': 'your-shared-secret'  // Read from Symfony config
  },
  body: JSON.stringify({ messages: [...] })
})
```

### CORS (Alternative to Nginx Proxy)

If skipping nginx proxy (dev only):

```typescript
export const mastra = new Mastra({
  server: {
    cors: {
      origin: ["http://127.0.0.1:8000"]
    }
  }
});
```

**Recommended**: Use nginx proxy (Phase 2) to avoid CORS complexity.

### Logging and Observability

Enable Pino logger:

```typescript
import { Mastra } from '@mastra/core';
import pino from 'pino';

const logger = pino({ level: 'info' });

export const mastra = new Mastra({
  logger,
  // ...
});
```

Future enhancements:
- OpenTelemetry traces
- Prometheus metrics
- Sentry error tracking

References: server-db/middleware.mdx, reference/observability/logging/pino-logger.mdx

---

## Phase 8 — Conversation Continuity with Memory ✅ COMPLETED

Enable multi-turn conversations with persistent memory using Mastra's thread and resource system.

### Problem Statement

Initial implementation (Phase 4) sent only single messages without conversation history. Each request was isolated, preventing follow-up questions or contextual conversations.

**Error encountered**:
```
Error: A resourceId and a threadId must be provided when using Memory.
Saw threadId "admin_superadmin_6c4ff7de8b5c7970" and resourceId "undefined"
```

Mastra's memory system requires **both** `threadId` and `resourceId` to enable conversation persistence and memory.

### Understanding Mastra Memory System

**Two-tier scoping**:
- **`resourceId`**: Represents the admin user (e.g., `admin_resource_superadmin`)
  - Stays constant across all conversations for same user
  - Enables resource-scoped memory (semantic recall across threads)
  - Format: `admin_resource_{userId}`

- **`threadId`**: Represents specific conversation (e.g., `admin_thread_superadmin_a3f2b8c1`)
  - Changes when "New Conversation" is clicked
  - Enables thread-scoped memory (conversation history)
  - Format: `admin_thread_{userId}_{randomHex}`

**Memory persistence**:
- **Thread-scoped**: Recent conversation history (last N messages)
- **Resource-scoped**: Semantic recall across all threads for same user
- Storage: LibSQL database (`mastra-service/.mastra/mastra.db`)

References: `memory/threads-and-resources.mdx`, `memory/overview.mdx`, `memory/working-memory.mdx`

### Controller Updates

**File**: `packages/framework/src/Controller/Admin/MastraController.php`

**1. Added session constant**:
```php
private const string SESSION_THREAD_KEY = 'mastra_thread_id';
```

**2. Added `getResourceId()` method**:
```php
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
```

**3. Added `getOrCreateThreadId()` method**:
```php
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
```

**4. Updated `dashboardAction()` to pass both IDs**:
```php
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
```

**5. Added `newConversationAction()` route**:
```php
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
```

### Template Updates — Chat Interface

**File**: `packages/administration/templates/content/mastra/index.html.twig`

Replaced single-message interface with full chat history UI.

**1. Chat header with thread/resource display**:
```twig
<div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">{{ 'Conversation'|trans }}</h3>
    <div>
        <small class="text-muted me-3">
            {{ 'Resource'|trans }}: <code id="resource-id-display">{{ resourceId }}</code> |
            {{ 'Thread'|trans }}: <code id="thread-id-display">{{ threadId }}</code>
        </small>
        <a href="{{ url('admin_mastra_newconversation') }}" class="btn btn-sm btn-outline-secondary">
            {{ 'New Conversation'|trans }}
        </a>
    </div>
</div>
```

**2. Scrollable chat container**:
```twig
<div class="card-body" style="max-height: 500px; overflow-y: auto; background-color: #f8f9fa;" id="chat-container">
    <div id="messages-list">
        <div class="text-muted text-center py-4">
            {{ 'Start a conversation by asking about the weather in any city.'|trans }}
        </div>
    </div>
</div>
```

**3. Message input form**:
```twig
<form id="mastra-form" onsubmit="return false;">
    <div class="mb-3">
        <label for="message-input" class="form-label">{{ 'Your message'|trans }}</label>
        <input id="message-input"
               class="form-control"
               type="text"
               placeholder="{{ 'Ask about weather, e.g., &quot;What\'s the weather in London?&quot;'|trans }}"
        />
    </div>
    <button id="send-btn" class="btn btn-primary" type="submit">
        {{ 'Send'|trans }}
    </button>
</form>
```

**4. Message styling**:
```css
.message {
    padding: 12px 16px;
    margin-bottom: 12px;
    border-radius: 8px;
    max-width: 85%;
}
.message-user {
    background-color: #0d6efd;
    color: white;
    margin-left: auto;
    text-align: right;
}
.message-assistant {
    background-color: white;
    border: 1px solid #dee2e6;
    margin-right: auto;
}
.message-loading {
    background-color: #e9ecef;
    margin-right: auto;
    font-style: italic;
    color: #6c757d;
}
.message-error {
    background-color: #f8d7da;
    border: 1px solid #f5c2c7;
    color: #842029;
    margin-right: auto;
}
```

**5. JavaScript conversation history management**:
```javascript
const threadId = '{{ threadId|e('js') }}';
const resourceId = '{{ resourceId|e('js') }}';
let conversationHistory = [];

async function sendMessage() {
    const message = input.value.trim();
    if (!message) return;

    input.value = '';
    sendBtn.disabled = true;

    // Add user message to UI and history
    addMessage('user', message);
    conversationHistory.push({ role: 'user', content: message });

    const loadingEl = addMessage('assistant', 'Loading...', 'loading');

    try {
        const res = await fetch('/mastra/api/agents/weatherAgent/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                messages: conversationHistory,
                threadId: threadId,
                resourceId: resourceId  // ← Critical: both IDs required
            })
        });

        loadingEl.remove();

        if (!res.ok) {
            const text = await res.text().catch(() => '');
            throw new Error(`HTTP ${res.status}: ${text}`);
        }

        const json = await res.json();
        const responseText = json?.text ?? JSON.stringify(json, null, 2);

        // Add assistant response to UI and history
        addMessage('assistant', responseText);
        conversationHistory.push({ role: 'assistant', content: responseText });

    } catch (err) {
        loadingEl.remove();
        const errorMessage = 'Error: ' + (err?.message || err);
        addMessage('assistant', errorMessage, 'error');
    } finally {
        sendBtn.disabled = false;
        input.focus();
    }
}
```

**Key JavaScript features**:
- Client-side conversation history array
- Sends full message array + threadId + resourceId to Mastra
- Auto-scroll to latest message
- Loading states per message
- Error handling with visual feedback
- Enter key support

### How It Works

**Flow diagram**:
```
┌─────────────────────────────────────────────────────────────┐
│ Admin User (superadmin)                                     │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│ MastraController                                            │
│ - getResourceId() → "admin_resource_superadmin"            │
│ - getOrCreateThreadId() → "admin_thread_superadmin_abc123" │
│ - Pass both to template                                     │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│ Admin Template (Twig + JS)                                  │
│ - Maintains conversationHistory[] in JavaScript             │
│ - Sends: { messages: [...], threadId, resourceId }         │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│ Nginx Proxy                                                 │
│ /mastra/api/* → http://mastra:4111/api/*                   │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│ Mastra Service (Node.js)                                    │
│ - Receives: { messages, threadId, resourceId }             │
│ - Memory system validates both IDs present                  │
│ - Stores conversation in LibSQL                             │
│ - Returns agent response                                    │
└─────────────────────────────────────────────────────────────┘
```

### Conversation Examples

**Example 1: Multi-turn weather conversation**
```
User: "What's the weather in London?"
Agent: "It's sunny, 18°C with light winds..."

User: "And in Paris?"  ← Agent remembers context (weather)
Agent: "Paris is cloudy, 15°C..."

User: "Which city is warmer?"  ← Agent remembers both cities
Agent: "London is warmer at 18°C compared to Paris at 15°C"
```

**Example 2: Session persistence**
```
1. User asks about weather in Berlin
2. User refreshes the page
3. Thread ID remains the same (stored in session)
4. User continues conversation - context preserved
```

**Example 3: New conversation**
```
1. User has conversation about London weather
2. Clicks "New Conversation" button
3. Session clears threadId, new one generated
4. ResourceId stays same (same admin user)
5. Fresh conversation starts
6. Mastra's semantic recall might still retrieve relevant past info
```

### Testing Checklist

**Basic flow**:
- [x] Single message works (no error about missing resourceId)
- [x] Follow-up messages maintain context
- [x] UI displays chat history correctly
- [x] Auto-scroll to latest message works
- [x] Loading states show during API calls
- [x] Error messages display properly

**Session persistence**:
- [x] Thread ID persists across page refreshes
- [x] Conversation continues after refresh
- [x] Different admins get different resource IDs

**New conversation**:
- [x] "New Conversation" button clears thread ID
- [x] New thread ID generated on next request
- [x] Resource ID remains constant
- [x] Previous conversation not accessible

**Memory system**:
- [x] ThreadId and resourceId sent to Mastra
- [x] Mastra accepts request without error
- [x] Conversation stored in LibSQL database
- [x] Agent demonstrates contextual awareness

### Implementation Status

**Completed** (2025-01-07):
- ✅ Controller session management (threadId + resourceId)
- ✅ Template chat interface with history
- ✅ JavaScript conversation state management
- ✅ Message styling (user vs assistant)
- ✅ Loading and error states
- ✅ "New Conversation" functionality
- ✅ Symfony cache cleared
- ✅ Manual testing successful

**Verified behavior**:
- No more "resourceId undefined" errors
- Multi-turn conversations work correctly
- Session persistence across page reloads
- Clean separation of threads and resources

### Future Enhancements

**Short-term**:
- [ ] Display conversation history on page load (query Mastra memory)
- [ ] Add timestamps to messages
- [ ] Show typing indicators
- [ ] Export conversation as PDF/text

**Medium-term**:
- [ ] Working memory display (show what agent remembers about user)
- [ ] Thread list sidebar (browse past conversations)
- [ ] Search within conversation history
- [ ] Thread titles auto-generation

**Long-term**:
- [ ] Streaming responses (real-time typing effect)
- [ ] Voice input/output
- [ ] Multi-agent conversations
- [ ] Conversation analytics (usage, topics, etc.)

References: `memory/conversation-history.mdx`, `memory/semantic-recall.mdx`, `reference/memory/memory-class.mdx`

---

## Definition of Done

### Phase 1-3 (Infrastructure)
- [ ] Mastra service runs: `pnpm run dev` exposes http://localhost:4111
- [ ] Docker Compose includes mastra service
- [ ] Nginx proxies `/mastra/api/*` to Mastra service
- [ ] Weather agent responds to test requests

### Phase 4 (Admin Integration)
- [ ] Controller exists: `packages/framework/src/Controller/Admin/MastraController.php`
- [ ] Menu item appears: Integrations → Mastra Assistant
- [ ] Template renders: `packages/administration/templates/content/mastra/index.html.twig`
- [ ] Admin page accessible: http://127.0.0.1:8000/admin/superadmin/mastra/dashboard
- [ ] Only super admins can access (403 for others)
- [ ] JavaScript successfully calls `/mastra/api/agents/weatherAgent/generate`
- [ ] Weather results display in UI

### Phase 5-6 (Storage & Deployment)
- [ ] Secrets configured via `.env` (no keys in repo)
- [ ] PostgreSQL storage configured with `mastra` schema
- [ ] Production build works: `pnpm run build && pnpm run start`

### Phase 7 (Hardening)
- [ ] Optional: Authentication middleware active
- [ ] Optional: Logging configured
- [ ] Optional: Monitoring/observability setup

### Phase 8 (Conversation Continuity) ✅ COMPLETED
- [x] Controller generates `resourceId` for admin user
- [x] Controller manages `threadId` via Symfony session
- [x] `newConversationAction()` route clears session thread
- [x] Template displays both threadId and resourceId
- [x] Chat interface with message history UI
- [x] JavaScript maintains conversation history array
- [x] Both IDs sent to Mastra API in requests
- [x] No "resourceId undefined" errors
- [x] Multi-turn conversations work with context
- [x] Session persistence across page refreshes
- [x] "New Conversation" button resets thread
- [x] Message styling (user vs assistant bubbles)
- [x] Loading states and error handling
- [x] Auto-scroll to latest message

---

## Next Steps and Future Enhancements

### Immediate Next Steps

1. **Additional Agents**: Create domain-specific agents
   - Customer support agent (order inquiries)
   - Product recommendation agent
   - Content generation agent

2. **Workflow Integration**: Build multi-step workflows
   - Order processing workflow
   - Product import validation workflow
   - Marketing campaign generation

3. ~~**Memory and Context**: Per-admin conversation persistence~~ ✅ **COMPLETED (Phase 8)**
   - ✅ ThreadId and resourceId implemented
   - ✅ Session-based conversation continuity
   - ✅ Chat interface with history
   - See Phase 8 documentation above for details

### Advanced Features

4. **Streaming Responses**: Real-time updates in UI
   ```javascript
   const response = await fetch('/mastra/api/agents/weatherAgent/stream', {
     method: 'POST',
     body: JSON.stringify({ messages: [...] })
   });

   const reader = response.body.getReader();
   // Stream chunks to UI
   ```

5. **Client SDK**: Use `@mastra/client-js` instead of raw fetch
   ```typescript
   import { MastraClient } from '@mastra/client-js';

   const client = new MastraClient({ baseUrl: '/mastra/api' });
   const result = await client.agents.weatherAgent.generate({ messages: [...] });
   ```

6. **Role-Based Permissions**: Fine-grained access control
   - Add `ROLE_MASTRA` for specific teams
   - Per-agent permission checks
   - Audit logging for agent usage

7. **Testing Strategy**:
   - **PHP**: Functional tests for controller routes
   - **Mastra**: Unit tests for agents/tools
   - **E2E**: Cypress tests for admin UI workflow

8. **Tool Integration**: Connect Mastra to Shopsys data
   - Custom tools to query products, orders, customers
   - Write operations (create orders, update inventory)
   - MCP servers for external APIs

### Package-First Extensions

Projects can extend the base implementation:

**Example**: Custom agent in project-base

```php
// project-base/src/Model/Mastra/CustomMastraFacade.php
namespace App\Model\Mastra;

class CustomMastraFacade
{
    public function callCustomAgent(string $prompt): array
    {
        // Project-specific agent logic
    }
}
```

```php
// project-base/src/Controller/Admin/CustomMastraController.php
namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\MastraController as BaseMastraController;

final class CustomMastraController extends BaseMastraController
{
    // Override or extend framework controller
}
```

---

## References

### Mastra Documentation
- Installation: `getting-started/installation.mdx`
- Agents: `agents/overview.mdx`, `agents/agent-memory.mdx`
- Workflows: `workflows/overview.mdx`
- Server: `server-db/production-server.mdx`, `server-db/runtime-context.mdx`
- Storage: `reference/storage/postgresql.mdx`
- Client: `server-db/mastra-client.mdx`, `reference/client-js/*`
- Observability: `reference/observability/logging/pino-logger.mdx`

### Shopsys Documentation
- Package-First Architecture: `CLAUDE.md` (Monorepo Architecture)
- Admin Menu: `docs/administration/administration-menu.md`
- Adding Admin Pages: `docs/cookbook/adding-a-new-administration-page.md`

### Implementation Files
- Controller: `packages/framework/src/Controller/Admin/MastraController.php`
- Menu: `packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php`
- Template: `packages/administration/templates/content/mastra/index.html.twig`
- Nginx: `docker/nginx/nginx.local.conf`
- Docker: `docker-compose.yml` (mastra service)
