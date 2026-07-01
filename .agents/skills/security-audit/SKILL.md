---
name: security-audit
description: >
  Shopsys-specific AI security audit of a pull request diff. Reviews changed code for real,
  exploitable security issues against OWASP Top 10 / CWE, grounded in how this repo implements
  each control. Runs the built-in /security-review as a high-confidence baseline, then layers
  Shopsys-specific checks, and posts findings as inline PR comments plus one summary comment. Use in CI on
  pull requests, or locally to audit branch changes.
user_invocable: true
version: 1.0.0
---

# Security Audit

**MINDSET: You are a security reviewer hunting for real, exploitable vulnerabilities in the changed code. You know this stack (Symfony + Doctrine + GraphQL backend, Next.js storefront, e-commerce). You report concrete issues with the exact fix and a CWE tag. You are advisory — you inform the developer, you do not block the merge.**

**Target:** `$ARGUMENTS`

- If `$ARGUMENTS` is a PR reference (`owner/repo#number`, a PR URL, or number), audit that PR's diff.
- If `$ARGUMENTS` is empty, audit the current branch's changes against its base branch.

---

## Phase 1: Scope the review

Audit the **changed lines plus the minimum surrounding context needed to judge a finding** — not the whole codebase.

- PR reference: read the diff with `gh pr diff <ref>` and metadata with `gh pr view <ref>`.
- No reference (local): `git --no-pager diff` (and `--stat`).
- For each changed sink, open the enclosing method / caller / interface with Read to confirm whether attacker-controlled input actually reaches it. A pattern match is a lead, not a finding — verify reachability before reporting.

Repo layout: backend lives in `packages/*` and `project-base/app/` (PHP/Symfony/Doctrine/GraphQL); the storefront is `project-base/storefront/` (Next.js/React/TypeScript). Apply the relevant sections below to whichever a hunk touches.

---

## Phase 2: Baseline pass — reuse the built-in `/security-review`

Before applying the checklist, run Anthropic's built-in **`/security-review`** command over the same diff to get a high-confidence baseline, then fold its findings into your output.

- Run `/security-review` via the Skill tool. In CI the checkout is the PR merge ref: the PR's changes are **committed**, so the working tree being clean is **not** a reason to skip — `/security-review` must review the current branch's committed diff against the merge-base of the base branch. Only report the baseline as unavailable if the invocation itself fails.
- It returns a **markdown report** and does **not** post any comments — you own all output, so there is no double-commenting. It reports only high-confidence, exploitable issues and deliberately **excludes** denial-of-service, rate-limiting, and generic input-validation findings.
- Your checklist below is the complement, not a duplicate: it backfills exactly those exclusions (GraphQL depth/complexity DoS, batching, N+1), adds Shopsys-specific and e-commerce business-logic coverage the general pass has no knowledge of, and applies this repo's known-safe filtering.
- Carry each baseline finding into the unified output: give it a severity, a CWE tag, `file:line`, and a confidence, and run it through the known-safe-patterns filter (drop any baseline finding that matches a documented safe convention here). Deduplicate against your own findings by `file:line` + root cause — never report the same issue twice.
- If `/security-review` does not run for any reason, note that one line in the summary and proceed with the checklist alone. Never skip the audit because the baseline was unavailable.

---

## Phase 3: What this checks against

Findings are graded against **OWASP Top 10 (2025)** and tagged with a **CWE ID** for traceability.

| OWASP 2025 | Category | Maps to sections below |
|---|---|---|
| A01 | Broken Access Control (now includes SSRF) | A, D, F, H |
| A02 | Security Misconfiguration | G, K, L |
| A03 | Software Supply Chain Failures | L (dependency/lockfile changes) |
| A04 | Cryptographic Failures | G |
| A05 | Injection (SQL, XSS, command, template) | B, C, D, J |
| A06 | Insecure Design (business-logic flaws) | H, I |
| A07 | Authentication Failures | A, K |
| A08 | Software or Data Integrity Failures | F |
| A09 | Security Logging & Alerting Failures | G |
| A10 | Mishandling of Exceptional Conditions (fail-open) | H, I, L |

Each item below is **risk → code smell to look for → CWE**.

---

## Phase 4: Checklist

### A. Access control & authorization — highest priority (A01)

- **Admin route/action with no access rule.** Shopsys admin is **deny-by-default**: `RouteAccessChecker` denies any admin route whose controller method has no access attribute. Smell: a new admin `#[Route]` method with none of `#[SuperAdminOnly]` / `#[RequireRole]` / `#[RequirePermission]` / `#[CanView|CanEdit|CanDelete]` / `#[PublicAccess]`; or `#[PublicAccess]` added to a sensitive action. **CWE-862, CWE-285.**
- **IDOR / BOLA — authz not scoped to the object.** Entity fetched by request `{id}`/`{uuid}` then a role check only, never an ownership check. In this repo, customer data must be scoped to the current user/company — e.g. `OrderRepository::findByUuidAndCustomerUser($uuid, $customerUser)`, and GraphQL resolvers must call `currentCustomerUser->findCurrentCustomerUser()` and query by the `customerUser`/`customer` entity. Smell: `->find($args['id'])` / `getReference(...)` returned without an owner (`customerUser`/`customer`) or domain filter; UUID-only lookups with no customer verification. **CWE-639, CWE-863.**
- **Cross-domain data leak.** Multidomain data must filter by the current `domainId` (resolved per-request by `DomainSubscriber` → `Domain::getId()`). Smell: a query over domain-scoped data (products, articles, settings) that omits `domainId`. **CWE-284, CWE-863.**
- **Authorization only in Twig / frontend.** `{% if is_granted(...) %}` or a storefront check hides UI, but the backing controller/GraphQL field has no matching server-side check. **CWE-602, CWE-285.**
- **Role decision made from raw roles.** `in_array('ROLE_ADMIN', $user->getRoles())` ignores role hierarchy — use `isGranted(...)`. **CWE-285, CWE-863.**
- **GraphQL field/mutation missing `@access`.** New Overblog field/mutation (`*.types.yaml` or `#[GQL\Field]`/`#[GQL\Mutation]`) with no `access:` / `#[GQL\Access]`; note queries resolve-then-null (missing `access` still runs the resolver), so per-resolver ownership checks are the real guard. **CWE-862, CWE-863.**
- **Messenger handler re-mutating by message id without re-checking authz** — async handlers run outside the HTTP firewall. **CWE-862.**

### B. Injection — Doctrine SQL/DQL (A05, CWE-89)

The repo convention is QueryBuilder/DQL with `setParameter()`, and native queries (`createNativeQuery`) also use `setParameter()` — that is safe; do **not** flag it. Flag only where input is concatenated into query text:

- **String-concatenated DQL / QueryBuilder expression** — `->where('... ' . $x)`, `->andWhere(sprintf(...))`, `createQuery("... '" . $input . "'")`, `->expr()->eq('u.x', $userInput)` with raw input as the value instead of a `:placeholder`. **CWE-89, CWE-943.**
- **Native/DBAL SQL with interpolation** — `executeQuery`/`executeStatement`/`fetch*`/`createNativeQuery` where the SQL string is built by concatenation/`$`-interpolation rather than `?`/`:name` + params. **CWE-89.**
- **ORDER BY / identifier injection (highest-signal Doctrine finding — not bindable).** `->orderBy($request->...)`, `->addOrderBy($sort)`, `"FROM " . $table`, `"SELECT " . $column` from input without an `in_array`/`match` allowlist. **CWE-89.**
- **LIKE with unescaped wildcards / IN() joined from input** — `LIKE '%" . $x . "%'`, `IN (" . implode(',', $ids) . ")`; use `:param` + `addcslashes($x,'%_')` / `ArrayParameterType`. **CWE-89.**

### C. XSS & template injection (A05, CWE-79)

- **Twig `|raw` / autoescape off on user data** — `{{ userVar|raw }}`, `{% autoescape false %}`, `new Environment(..., ['autoescape' => false])`. **CWE-79, CWE-116.**
- **Server-side template injection** — user input compiled as a template: `$twig->createTemplate($userInput)`, `->render('...' . $userInput)`, template body from DB/CMS. **CWE-1336, CWE-94.**
- **React `dangerouslySetInnerHTML` on untrusted HTML** — flag when `__html` derives from user input and is not sanitized (DOMPurify). In this repo, backend GraphQL HTML fields (product/blog/advert descriptions, GrapesJS content) flow into `dangerouslySetInnerHTML` — confirm they are sanitized server-side; the `window.__ENV` injection in `_document.tsx` is already `<`-escaped, do not flag it. **CWE-79.**
- **Wrong-context encoding** — `{{ var }}` inside `<script>` without `|escape('js')`; `href="{{ var }}"` allowing `javascript:`; `{{ x|json_encode|raw }}` without HEX flags. **CWE-79, CWE-116, CWE-83.**
- **API returning `text/html`** instead of `application/json` for user-controlled data (browser sniffs → stored XSS). **CWE-79.**

### D. GraphQL-specific (A01/A05)

- **Depth / complexity / introspection are OFF by default** in graphql-php and Overblog. Smell: a new/expanded GraphQL surface with no `query_max_depth` / `QueryDepth`, no `query_max_complexity` / `QueryComplexity` (this repo uses `ConnectionAwareQueryComplexity` for connection/pagination cost — check new list/connection fields are counted by it or declare their own complexity), or `enable_introspection: true` hard-coded rather than gated on `%kernel.debug%`. **CWE-770, CWE-400, CWE-200.**
- **Per-resolver IDOR** — see section A (resolver returns an entity by id with no ownership check). **CWE-639.**
- **Batching / alias abuse** — array-batched or heavily-aliased operations bypass per-request rate limits on sensitive mutations (`login`, `resetPassword`, `applyCoupon`). **CWE-770, CWE-307.**
- **N+1 as DoS** — DB query inside an item-level resolver with no batch loader, combined with missing depth limits. **CWE-770.**
- **Debug leakage** — `DebugFlag::INCLUDE_DEBUG_MESSAGE|INCLUDE_TRACE`, Overblog `show_debug_info: true` outside dev. **CWE-209.**

### E. Mass assignment & input validation (CWE-915)

- **Entity-backed form with `allow_extra_fields => true`**, or `->add('roles'|'isAdmin'|'price'|'enabled'|'status'|'owner')` on an entity form without `'mapped' => false`. **CWE-915, CWE-269.**
- **Hydrating an entity from a raw request array** — `foreach ($request->request->all() ...)`, `->fromArray($data)`, `denormalize`/`deserialize` / `OBJECT_TO_POPULATE` onto an entity with no `'groups'`/`ATTRIBUTES` allowlist. **CWE-915.**
- **Missing validation on a new GraphQL mutation** — the repo validates via Overblog `InputValidator->validate($groups)` + Symfony constraints (incl. custom ownership validators like `DeliveryAddressUuidValidator`). Smell: a mutation that reads `$argument['input']` and acts without calling the validator. **CWE-20.**

### F. Deserialization, SSRF, file upload (A08/A01)

- **`unserialize()` on untrusted input** (request/cookie/cache) without `['allowed_classes' => false]`; `phar://`/`data://` paths into file functions; Symfony `deserialize($data, $userType, ...)` with attacker-controlled type; `XmlEncoder`/`format:'xml'` (XXE). **CWE-502, CWE-611.**
- **SSRF** — Symfony HttpClient / Guzzle / `file_get_contents` fetching a user-controlled URL with no scheme + host allowlist and no `max_redirects: 0`. Reaches internal services / cloud metadata. **CWE-918.**
- **File upload** — trusting `getClientMimeType()`/`getClientOriginalExtension()`; missing `#[Assert\File/Image]` size+type limits; client filename used as the on-disk path (traversal); storing under `public/`; VichUploader `OrignameNamer` preserving the client name. **CWE-434, CWE-22.**

### G. Secrets & sensitive-data exposure (A02/A04)

- **Hardcoded secret in the diff** — flag added lines matching credential patterns: `AKIA[0-9A-Z]{16}`, `sk_live_…`, `gh[pousr]_…`, `-----BEGIN … PRIVATE KEY-----`, `(?i)(secret|token|password|api[_-]?key)\s*[:=]\s*['"][^'"]{8,}['"]`, `scheme://user:pass@host`. Ignore placeholders (`example`, `changeme`, `<...>`, `.env.example`). In this repo secrets must come via `%env(...)%` (e.g. `GOPAY_CONFIG`, `PACKETERY_API_PASSWORD`, `APP_SECRET`, `DATABASE_*`) — flag literals in `services.yaml`/`*.yaml`/`.env` (non-`.local`) or test fixtures. **CWE-798, CWE-259, CWE-321.**
- **Secret leaked to the client bundle (storefront).** The client-exposed allowlist is `buildPublicEnvConfig.ts` (surfaced via `window.__ENV`). Smell: a credential-looking key added there; a server-only value (e.g. `INTERNAL_ENDPOINT`, `SENTRY_AUTH_TOKEN`, `REDIS_*`) read in a `'use client'` module or passed as a prop to a client component. **CWE-200, CWE-798.**
- **Sensitive field newly exposed via Serializer/GraphQL** — `password`, `passwordHash`, `token`, `refreshToken`, `apiKey`, `iban` added to a `#[Groups]`-serialized class or a GraphQL type without a restrictive group / `#[Ignore]`. **CWE-200, CWE-359.**
- **Verbose errors / debug in prod** — `APP_DEBUG=1`, `dump(`/`dd(`/`var_dump(`/`phpinfo()`, returning `$e->getMessage()`/`getTraceAsString()` to the client, profiler outside dev. **CWE-209, CWE-489.**
- **Secrets/PII in logs** — logging `$request->getContent()`, `Authorization` headers, cookies, passwords, or card/payment data; user input concatenated into a log message (log injection). **CWE-532, CWE-117.**

### H. E-commerce business logic (A06 Insecure Design — highest value, lowest tooling)

- **Trusting client-sent price/total/discount/currency** — cart/order/payment handler persisting or charging an amount read from the request instead of re-deriving it server-side from the product. **CWE-602, CWE-472.**
- **Negative / huge / non-integer quantity** used without `> 0`, upper-bound, and integer checks. **CWE-20, CWE-190.**
- **Coupon/voucher abuse & stacking** — no per-user/usage-count/already-redeemed check; discount not re-validated against the final cart at placement. **CWE-840, CWE-799.**
- **Coupon / stock race (TOCTOU)** — check-then-decrement on stock/coupon without a transaction, row lock, or atomic conditional update. **CWE-362, CWE-367.**
- **Order/payment state manipulation & step-skipping** — `status`/`isPaid` settable from the request; confirmation reachable without verifying prior steps; success URL treated as proof of payment. **CWE-841, CWE-840.**
- **Money as float** — line-item `toFixed`/`Math.round` before summing, or float math on money instead of integer minor units / a decimal type. **CWE-682, CWE-1339.**

### I. Payments & webhooks (A06/A10)

- **Payment callback trusts its own body.** In this repo, GoPay's `front_order_payment_status_notify` (`PaymentStatusNotifyController::notifyAction`) is unsigned but **re-fetches** transaction status from the GoPay API via `PaymentServiceFacade::updatePaymentTransactionsByOrder()` — that re-fetch is the control. Flag any **new** payment/webhook handler that instead marks an order paid/fulfilled directly from the request payload or a client redirect, without a server-to-server status re-fetch or signature verification. **CWE-345, CWE-347, CWE-602.**
- **Replay / non-idempotent callback** — no freshness check and no idempotency guard (unique on event id / "already processed?") before fulfilling or refunding. **CWE-294, CWE-799, CWE-837.**

### J. Next.js / React storefront (A01/A05)

- **Server Action / route handler missing authz or validation** — a `'use server'` function or `app/**/route.ts` doing a mutation without an early auth + permission check and a runtime validator (types are not enforced at runtime). **CWE-862, CWE-20.**
- **Auth relied on only in `middleware.ts`** (CVE-2025-29927 lets middleware be skipped) — re-check in the handler/data layer; verify the Next.js version in `package.json` is patched. **CWE-285.**
- **SSRF via server `fetch(userUrl)` or the image optimizer** — `next.config` `images.remotePatterns` with wildcard hosts; proxy/"fetch this URL" handlers. **CWE-918.**
- **Open redirect** — `redirect(searchParams.x)` / `NextResponse.redirect(userValue)` without an allowlist (a `startsWith('/')` check is bypassable via `//evil.com`). **CWE-601.**
- **Insecure/auth cookies** — session/refresh cookie set without `httpOnly`+`secure`+`SameSite`. In this repo `setTokensToCookies.ts` sets httpOnly+secure domain-scoped cookies — flag regressions. **CWE-1004, CWE-614, CWE-1275.**
- **Caching per-user data** — `fetch(userUrl, { cache: 'force-cache' })` / `revalidate` on authenticated data (should be `no-store`). **CWE-524.**
- **Prototype pollution** — deep-merge of a request body (`lodash.merge`/custom) or `obj[userKey] = value` with an unfiltered key. **CWE-1321.**

### K. CSRF, cookies, sessions (A07, CWE-352)

- **CSRF disabled on a state-changing Symfony form** (`'csrf_protection' => false`), or a manual POST/DELETE action mutating state with no `isCsrfTokenValid` / `#[IsCsrfTokenValid]`. **CWE-352.**
- **State change on a GET** — `methods: ['GET']` (or unspecified) on a writing/deleting action; SameSite=Lax does not protect this. **CWE-352, CWE-650.**
- **Login/logout CSRF disabled**, or `cookie_samesite: none`/`cookie_secure: false`/`cookie_httponly: false` weakened in `framework.yaml`. **CWE-352, CWE-1275, CWE-614.**
- **Stateless/GraphQL CSRF** — the JSON API resists CSRF only while it refuses browser-forgeable requests; flag config accepting GET execution or `x-www-form-urlencoded`/`multipart`/`text/plain` on mutations. **CWE-352.**

### L. Configuration & supply chain (A02/A03/A10)

- **Permissive CORS** (`Access-Control-Allow-Origin: *` with credentials), removed security headers / CSP, `Options +Indexes`, XML parser without XXE hardening, default creds in seed/config. **CWE-16, CWE-611, CWE-1021.**
- **Dependency risk** — bump to a version with a known CVE, a new dep from an unpinned/unvetted source, or a loosened/removed lockfile. **CWE-1104, CWE-937.**
- **Fail-open error handling** — a `catch` that swallows a security-relevant error and continues (e.g. commits a transaction despite a validation exception). **CWE-703, CWE-755.**

---

## Known-safe patterns in this repo (do NOT flag)

- QueryBuilder/DQL **and** `createNativeQuery` that use `setParameter()` — the repo's normal, safe convention.
- `window.__ENV` in `_document.tsx` — the injected config is `<`-escaped and only carries the `buildPublicEnvConfig.ts` allowlist.
- GoPay `notifyAction` lacking a signature — mitigated by the server-side status re-fetch (`updatePaymentTransactionsByOrder`). Only flag *new* handlers that skip the re-fetch.
- Admin routes without an attribute are already denied by `RouteAccessChecker` — flag the *missing attribute* (so intent is explicit), not an imagined open route.
- `%env(...)%` references in config are correct — flag hardcoded literals, not env references.

---

## Highest-signal greps (prioritize these — most likely real, not nitpicks)

1. Request input concatenated into DQL/DBAL/QueryBuilder text, or reaching `orderBy`/`addOrderBy` without an allowlist → SQLi.
2. Entity fetched by request id and returned/mutated with no owner (`customerUser`/`customer`) or `domainId` check → IDOR/BOLA (controllers and GraphQL resolvers alike).
3. Client-sent price/quantity/discount/payment-status trusted server-side → business-logic / payment abuse.
4. New GraphQL surface with no depth/complexity/introspection control; new field/mutation with no `access:` or ownership check.
5. `allow_extra_fields => true` on an entity form; `denormalize`/`OBJECT_TO_POPULATE` onto an entity without `groups`.
6. Hardcoded secret literal, or a credential-shaped key added to `buildPublicEnvConfig.ts` / read in a client component.
7. `unserialize(` on untrusted data; `|raw`/`createTemplate(` on user input; user URL into HttpClient/`fetch` without an allowlist.
8. New payment/webhook handler marking an order paid from the request body without a server-side re-fetch or signature check.

---

## Severity & confidence

Rate every finding on **both** axes.

| Severity | Use when |
|---|---|
| Critical | Directly exploitable now: injection, auth bypass, secret leak, IDOR on sensitive data, unsigned fulfillment |
| High | Exploitable with a realistic precondition, or sensitive-data exposure |
| Medium | Needs an unlikely chain, or a real hardening gap |
| Low | Minor hardening / defense-in-depth |

**Confidence: High / Medium / Low.** Report every issue you can substantiate. Do **not** silently drop a plausible finding because you are unsure — mark it **Low confidence** and state what you'd need to confirm it. Use the known-safe patterns above to avoid re-flagging this repo's conventions; that is how you keep false positives down, not by withholding uncertain findings.

---

## Phase 5: Post the result

### Inline comments (line-specific findings)

Before posting anything, list the inline comments that already exist on the PR, so re-runs don't repeat findings that were already reported:

```bash
gh api "repos/<owner>/<repo>/pulls/<pr-number>/comments" --paginate \
    --jq '.[] | select(.line != null) | {path, line, body: (.body | split("\n")[0])}'
```

Skip a finding when an existing comment sits on the same `path` + `line` and describes the same issue — match by CWE and root cause, not by verbatim wording, since phrasing varies between runs. Post the finding only when it is new, has moved to a different line, or has materially changed (e.g. the severity was upgraded). The `select(.line != null)` filter deliberately ignores outdated comments on code that has since changed — a finding that reappears on the reworked code deserves a fresh comment.

For each finding that maps to a changed line, post an inline comment on that `file:line` with `mcp__github_inline_comment__create_inline_comment` (`confirmed: true`). Anchor only to lines in the PR diff. Body:

```
**[Critical · CWE-89] SQL injection** — `$sortField` from the request is concatenated into ORDER BY; an attacker controls the query. Confidence: High.
Fix: allowlist the sort column — `match($sortField) { 'name' => 'p.name', 'price' => 'p.price', default => throw ... }`.
```

Lead with `**[Severity · CWE-XXX] Title**`, then why it's exploitable, the confidence, and the exact fix.

### Summary comment (always, one per PR)

Post a single sticky comment — counts by severity, a pointer to the inline notes, and any finding that doesn't map to a single changed line:

```
<!-- claude-security-audit -->
## 🔒 AI security audit

_Advisory review of the PR diff against OWASP Top 10 / CWE — not a merge gate._

**Findings:** Critical 1 · High 0 · Medium 2 · Low 1 — line-specific ones are inline on the diff.

### Not tied to a single line
- [Medium · CWE-770] New GraphQL `articles` list field adds no complexity cost and depth limiting is disabled — a nested query can exhaust the DB. Confidence: Medium. Fix: declare `complexity` on the field / enable `query_max_complexity`.
```

Keep it as one comment across re-runs so the record isn't duplicated. Find the previous summary by its `<!-- claude-security-audit -->` marker and update it in place; create it only when no marked comment exists:

```bash
COMMENT_ID=$(gh api "repos/<owner>/<repo>/issues/<pr-number>/comments" --paginate \
    --jq '[.[] | select(.body | startswith("<!-- claude-security-audit -->"))][0].id')
if [ -n "$COMMENT_ID" ] && [ "$COMMENT_ID" != "null" ]; then
    gh api -X PATCH "repos/<owner>/<repo>/issues/comments/$COMMENT_ID" -f body="<the markdown above>"
else
    gh pr comment <pr-number> --repo <owner>/<repo> --body "<the markdown above>"
fi
```

Never use `gh pr comment --edit-last` — it edits the current user's most recent comment on the PR regardless of which workflow or tool created it, so it can overwrite an unrelated comment posted by the same bot account.

If there are no findings, post only the summary: `<!-- claude-security-audit -->` + `## 🔒 AI security audit` + `No security issues found in the changed code.` — and no inline comments.

---

## Rules

- **Diff only**: audit the changed code plus the minimum context needed to judge a finding.
- **Verify reachability**: prove attacker-controlled input reaches the sink before rating a finding High/Critical.
- **Coverage with confidence**: report substantiated *and* plausible issues, each tagged severity + CWE + confidence; don't suppress the uncertain ones — down-rank them.
- **Baseline + checklist, merged**: fold the `/security-review` baseline findings and your checklist findings into one deduplicated result (by `file:line` + root cause); apply the known-safe filter to both.
- **Respect this repo's conventions**: use the known-safe list to avoid noise.
- **Advisory**: never imply the PR is blocked. Inform, don't gate.
- **Be specific**: exact `file:line`, exact remediation, exact CWE.
