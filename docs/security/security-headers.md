# Security Headers

Shopsys Platform uses HTTP security headers to reduce common browser-side risks such as XSS, clickjacking, MIME sniffing, and insecure transport.

This page focuses on what each header does and the default platform behavior.
For implementation details, see:

- nginx configuration:
    - `project-base/docker/nginx/nginx.conf` (development)
    - `project-base/app/orchestration/kubernetes/configmap/nginx.yaml` (Kubernetes)
- backend listener: `packages/framework/src/Component/HttpFoundation/SecurityHeadersResponseListener.php`
- storefront SSR: `project-base/storefront/utils/serverSide/initServerSideProps.ts`

## Headers Reference

### Content-Security-Policy

**In plain English:** A whitelist that tells the browser "only load scripts, styles, images, and other resources from these trusted sources." If an attacker injects malicious code into your page, the browser will refuse to run it because it's not on the whitelist. This is the primary defense against XSS and content injection attacks.

- Default policy includes:
    - `frame-ancestors 'self'`
    - `default-src 'self' https: 'unsafe-inline' data:`
    - `script-src 'self' 'unsafe-inline'` plus trusted third-party sources used by the platform
- Why these values:
    - `frame-ancestors 'self'` blocks clickjacking from other origins
    - `default-src 'self' https: ...` keeps resource loading restrictive while allowing standard HTTPS integrations
    - `script-src` explicitly allows only platform-required script sources
- Configurable by superadmin in: `Admin > Superadmin > CSP Header Setting`
- Applied to HTML responses (admin/storefront); not intended as a protection mechanism for static files or API payloads

### X-Frame-Options

**In plain English:** Prevents other websites from putting your pages inside a hidden frame. Without this, an attacker could overlay your admin or checkout page with their own site and trick users into clicking your buttons without realizing it (clickjacking).

- Value: `SAMEORIGIN`
- Allows same-origin embedding where needed
- Why this value: blocks third-party framing while preserving same-origin embeds required by admin tooling

### X-Content-Type-Options

**In plain English:** Tells the browser "trust the file type I'm telling you, don't try to guess." Without this, a browser might look at a file labeled as an image, decide it looks like JavaScript, and execute it - which an attacker could exploit by uploading a malicious file disguised as an image. Particularly important for e-commerce platforms that accept user-uploaded content.

- Value: `nosniff`
- Why this value: prevents MIME confusion attacks where non-script files could be interpreted as executable content

### Referrer-Policy

**In plain English:** Controls what URL information is shared when a user clicks a link to another site. With `strict-origin-when-cross-origin`, same-origin requests still receive the full referrer, while cross-origin HTTPS requests receive only the origin (for example, `https://shop.example`) without path/query details.

- Value: `strict-origin-when-cross-origin`
- Same-origin requests keep full referrer, cross-origin requests send only origin
- Why this value: balances analytics/integration needs with protection against leaking sensitive URL path/query data

### Strict-Transport-Security (HSTS)

**In plain English:** Tells the browser "from now on, only connect to this site over HTTPS - even if the user types `http://`." Once a browser sees this header, it will automatically upgrade all future requests to HTTPS for the specified duration. This prevents man-in-the-middle attacks where an attacker intercepts the initial insecure HTTP request before the server can redirect to HTTPS.

- Value: `max-age=31536000; includeSubDomains`
- Enforced by browsers only when first received over valid HTTPS
- Why this value:
    - `max-age=31536000` enforces HTTPS for one year
    - `includeSubDomains` closes weaker subdomain downgrade paths
- Limitation: without preload, the very first HTTP visit can still be exposed to SSL stripping before the browser learns the HSTS policy
- For highly security-oriented deployments, consider enabling `preload` and registering the domain at `https://hstspreload.org/` after validating preload requirements

### X-XSS-Protection

**In plain English:** Older browsers had a built-in XSS filter (XSS Auditor) that tried to detect and block attacks, but it was unreliable and could actually be exploited by attackers to break legitimate pages. Setting this to `0` turns it off. Modern browsers have removed it entirely (Chrome 78+, Edge 79+, Firefox) - CSP is the proper replacement.

- Value: `0`
- Modern protection should rely on CSP instead
- Why this value: legacy XSS auditors are obsolete and can cause unsafe/incorrect behavior

### X-Powered-By

**In plain English:** Upstreams (PHP/Next.js) may expose technology/version details in `X-Powered-By`. Attackers use this to target known vulnerabilities. We overwrite it with a generic `Shopsys Platform` value so no useful stack/version information is exposed.

- Value: `Shopsys Platform`
- Why this value: keeps useful platform identification while avoiding technology/version fingerprinting

### Permissions-Policy

**In plain English:** Controls access to browser features such as camera, microphone, geolocation, payment APIs, and USB. Most features are disabled for all origins using `()`. Geolocation is allowed only for same-origin storefront responses according to deployment security policy.

- Platform default is restrictive (most features disabled)
- Why this value style: least-privilege default reduces browser feature abuse surface

### Access-Control-Allow-Origin (CORS)

**In plain English:** By default, browsers block JavaScript on `site-a.com` from fetching resources from `site-b.com`. This header says "it's OK, these origins are allowed to load my resources." The wildcard `*` means "anyone can load this" - used here only for public static assets.

- Public static assets can be exposed for cross-origin loading
- Sensitive dynamic endpoints are expected to remain same-origin
- Why this value style: `*` for public assets improves interoperability, while avoiding permissive CORS on sensitive endpoints

### Access-Control-Allow-Credentials

**In plain English:** Even when cross-origin requests are allowed (via `Access-Control-Allow-Origin`), this header controls whether the browser sends cookies or login tokens along with those requests. Set to restrictive behavior - so an external site can load a public image, but cannot make requests as a logged-in user. Browsers also enforce this: when `Access-Control-Allow-Origin` is `*`, credentials are always blocked regardless.

- Default behavior is restrictive for public cross-origin assets
- Why this value style: prevents authenticated cross-origin usage patterns that could increase CSRF/session abuse risk

## Additional Hardening

**In plain English:** nginx normally includes its version number in every response (for example, `Server: nginx/1.25.3`). Turning `server_tokens` off removes the version, so attackers cannot scan for servers running vulnerable nginx versions. Same idea as X-Powered-By above.

- `server_tokens` is disabled to avoid exposing nginx version details in responses
