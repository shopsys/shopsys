# Mastra React UI Embed for Admin SQL Assistant

## Overview

Replace the custom Twig/vanilla-JS SQL chat UI with a pluggable React chat UI, embedded into the existing admin page as a React island.

Recommended stack for this repository:
- UI library: `CopilotKit` (lowest custom UI effort for this codebase)
- Mastra transport: CopilotKit route (`registerCopilotKit`)
- Embedding model: React mount inside existing Twig page (no full page rewrite)

## Current State Analysis

Key findings from current implementation:
- SQL chat page has extensive custom UI logic in Twig script: markdown parsing, tool rendering, retries, history hydration, SQL approval state machine.
  - [sql.html.twig](/home/rosta/Projects/shopsys/packages/administration/templates/content/mastra/sql.html.twig#L142)
  - [sql.html.twig](/home/rosta/Projects/shopsys/packages/administration/templates/content/mastra/sql.html.twig#L251)
  - [sql.html.twig](/home/rosta/Projects/shopsys/packages/administration/templates/content/mastra/sql.html.twig#L479)
  - [sql.html.twig](/home/rosta/Projects/shopsys/packages/administration/templates/content/mastra/sql.html.twig#L672)
- Admin controller already provides `threadId` and `resourceId` from session and is superadmin-protected.
  - [MastraController.php](/home/rosta/Projects/shopsys/packages/framework/src/Controller/Admin/MastraController.php#L14)
  - [MastraController.php](/home/rosta/Projects/shopsys/packages/framework/src/Controller/Admin/MastraController.php#L89)
- Nginx already proxies `/mastra/*` to `mastra-service`.
  - [nginx.conf](/home/rosta/Projects/shopsys/project-base/docker/nginx/nginx.conf#L120)
- `mastra-service` currently exposes agents and tools, but no UI-specific route registration yet.
  - [index.ts](/home/rosta/Projects/shopsys/mastra-service/src/mastra/index.ts#L1)
- `sqlAgent` approval is currently prompt-driven (text instruction), not explicit tool approval API flow.
  - [sql-agent.ts](/home/rosta/Projects/shopsys/mastra-service/src/mastra/agents/sql-agent.ts#L1)

## Desired End State

- SQL assistant page still lives under Symfony admin route (`/superadmin/mastra/sql`).
- Twig template renders a mount node and minimal bootstrap data only.
- React app (CopilotKit) renders chat UX.
- Conversation memory remains session-scoped with existing `threadId`/`resourceId`.
- Mastra API calls are authenticated and user-isolated.
- Legacy custom JS chat logic is removed.

## What We Are Not Doing

- No full admin SPA rewrite.
- No replacement of Symfony session/thread generation model.
- No migration of all Mastra pages in one step (weather page can stay as-is initially).
- No direct trust of client-provided `resourceId` without server-side enforcement.

## Implementation Approach

We will migrate incrementally with a parity-first strategy:
1. Introduce React shell + CopilotKit UI.
2. Keep current behavior parity (including textual "approve/execute" interaction).
3. Harden auth/authorization in Mastra middleware.
4. Remove legacy Twig JS after validation.
5. Optionally upgrade to explicit tool approval buttons in phase 2.

---

## Phase 1: Backend Readiness for Pluggable UI

### Changes Required

1. Add Mastra UI route integration package(s).
   - `@ag-ui/mastra`, `@copilotkit/runtime` and related peer deps (per Mastra docs).
   - Files:
     - [mastra-service/package.json](/home/rosta/Projects/shopsys/mastra-service/package.json)

2. Register CopilotKit-compatible route in Mastra server.
   - Implemented route: `registerCopilotKit({ path: '/chat', resourceId: 'shopsys-admin' })`
   - Build note: requires `bundler.externals: true` in current environment
   - Files:
     - [mastra-service/src/mastra/index.ts](/home/rosta/Projects/shopsys/mastra-service/src/mastra/index.ts)

3. Keep existing `/api/agents/*` routes unchanged for safe rollback.

### Success Criteria

#### Automated Verification
- [x] Mastra service builds: `docker compose exec mastra pnpm run build`
- [x] Mastra service starts: `docker compose exec mastra pnpm run dev`

#### Manual Verification
- [x] Copilot-compatible route responds at `/mastra/chat` through nginx proxy.
- [x] Existing agents API remains reachable at `/mastra/api/agents`.
- [ ] Existing `/mastra/api/agents/sqlAgent/generate` returns successful model output (currently blocked by model config: `gemini-3.1-flash` not found).

---

## Phase 2: Security and Context Enforcement

### Changes Required

1. Add Mastra server auth.
   - Use JWT auth provider (`MastraJwtAuth`) or custom auth provider.
   - Validate Bearer token on protected routes.
   - Files:
     - [mastra-service/src/mastra/index.ts](/home/rosta/Projects/shopsys/mastra-service/src/mastra/index.ts)

2. Add middleware to enforce resource isolation.
   - Set `MASTRA_RESOURCE_ID_KEY` from authenticated user identity.
   - Optionally set `MASTRA_THREAD_ID_KEY` when thread is validated.

3. Generate short-lived token in Symfony admin controller and pass to Twig.
   - Add token issuance helper/service and inject value into page payload.
   - Files:
     - [MastraController.php](/home/rosta/Projects/shopsys/packages/framework/src/Controller/Admin/MastraController.php)

4. Add shared secret env wiring for PHP + Mastra containers.
   - File:
     - [docker-compose.yml](/home/rosta/Projects/shopsys/docker-compose.yml)

### Success Criteria

#### Automated Verification
- [ ] PHP compiles and standards pass: `docker compose exec php-fpm php phing standards-fix`
- [ ] Mastra service build passes: `docker compose exec mastra pnpm run build`

#### Manual Verification
- [ ] Anonymous calls to protected Mastra routes return `401`.
- [ ] Authenticated admin calls succeed.
- [ ] Cross-user `resourceId` spoof attempts are rejected/ignored.

---

## Phase 3: React Island in Admin Template

### Changes Required

1. Add React support to admin asset build.
   - Enable React preset and add dedicated entrypoint.
   - File:
     - [project-base/app/webpack.config.js](/home/rosta/Projects/shopsys/project-base/app/webpack.config.js)

2. Add React + CopilotKit deps to JS build context.
   - File:
     - [project-base/app/package.json](/home/rosta/Projects/shopsys/project-base/app/package.json)

3. Implement SQL assistant React app entry and mount logic.
   - New files in admin assets, e.g.:
     - `packages/administration/assets/src/js/mastra/sqlAssistantApp.tsx`
     - `packages/administration/assets/src/js/mastra/mountSqlAssistant.tsx`

4. Replace Twig page script with:
   - root mount `<div id="mastra-sql-root" ...>`
   - data attributes (`threadId`, `resourceId`, auth token)
   - entrypoint include.
   - File:
     - [sql.html.twig](/home/rosta/Projects/shopsys/packages/administration/templates/content/mastra/sql.html.twig)

### Success Criteria

#### Automated Verification
- [ ] Asset build passes: `docker compose exec php-fpm php phing npm-dev`
- [ ] JS standards pass: `docker compose exec php-fpm php phing standards-fix`

#### Manual Verification
- [ ] SQL assistant page renders Copilot chat component.
- [ ] Message send/receive works from admin screen.
- [ ] Existing admin layout/navigation remains unchanged.

---

## Phase 4: Conversation and SQL Workflow Parity

### Changes Required

1. Preserve existing thread/resource behavior.
   - Continue using Symfony session-generated `threadId` and `resourceId`.
   - Send as memory config from React transport.

2. Implement parity approval flow first.
   - Keep current "show SQL, ask for confirmation" behavior via agent prompt flow.
   - Do not block migration on custom button UX.

3. Optional hardening (recommended phase 2):
   - Move to explicit tool-level approval (`requireApproval`) for SQL execution tool.
   - If adopted, add explicit approve/decline controls in React layer.
   - Files:
     - [sql-execution-tool.ts](/home/rosta/Projects/shopsys/mastra-service/src/mastra/tools/sql-execution-tool.ts)
     - [sql-agent.ts](/home/rosta/Projects/shopsys/mastra-service/src/mastra/agents/sql-agent.ts)

### Success Criteria

#### Automated Verification
- [ ] Mastra tool code compiles: `docker compose exec mastra pnpm run build`

#### Manual Verification
- [ ] Conversation history loads correctly on page reload.
- [ ] SQL is not executed before user confirmation.
- [ ] Query result rendering remains readable/useful for admins.

---

## Phase 5: Cutover and Cleanup

### Changes Required

1. Remove legacy Twig JS chat implementation from SQL template.
2. Keep rollback switch for one release cycle (feature flag or route toggle).
3. Update internal docs for the new admin chat architecture.

### Success Criteria

#### Automated Verification
- [ ] No dead references to removed legacy functions.
- [ ] CI checks pass (`make check-fix`).

#### Manual Verification
- [ ] SQL assistant works end-to-end after deployment restart.
- [ ] No regressions in admin navigation or other integration screens.

---

## Top Risks and Mitigations

1. Auth bypass on `/mastra/*`
- Mitigation: enforce JWT auth + middleware resource isolation in Mastra.

2. CSS/JS conflicts between admin and React UI
- Mitigation: isolate React app styles and mount in dedicated root.

3. Approval semantics regression (unsafe SQL execution)
- Mitigation: parity-first rollout + explicit approval hardening phase.

4. Build pipeline friction adding React to legacy admin bundle
- Mitigation: dedicated entrypoint only; minimal touch to existing admin entries.

5. Difficult rollback if done as big-bang
- Mitigation: keep old API routes and route-level fallback during cutover.

## Testing Strategy

### Unit/Static Checks
- Mastra build: `docker compose exec mastra pnpm run build`
- PHP standards: `docker compose exec php-fpm php phing standards-fix`
- Full checks before merge: `make check-fix`

### Manual Scenarios
1. Superadmin opens SQL assistant page and sees React UI.
2. Send question -> receive SQL proposal -> approve -> see results.
3. Refresh page -> same conversation history appears.
4. Start new conversation -> new thread behavior works.
5. Attempt unauthorized API access without token -> blocked.

## References

- Mastra docs:
  - `guides/build-your-ui/copilotkit.md`
  - `guides/build-your-ui/assistant-ui.md`
  - `guides/build-your-ui/ai-sdk-ui.md`
  - `docs/agents/agent-approval.md`
  - `docs/server/middleware.md`
  - `docs/server/request-context.md`
  - `reference/auth/jwt`
  - `reference/ai-sdk/chat-route`
- Existing implementation:
  - [sql.html.twig](/home/rosta/Projects/shopsys/packages/administration/templates/content/mastra/sql.html.twig)
  - [MastraController.php](/home/rosta/Projects/shopsys/packages/framework/src/Controller/Admin/MastraController.php)
  - [nginx.conf](/home/rosta/Projects/shopsys/project-base/docker/nginx/nginx.conf)
  - [mastra index](/home/rosta/Projects/shopsys/mastra-service/src/mastra/index.ts)
