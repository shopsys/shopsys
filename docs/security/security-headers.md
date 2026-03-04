# Security Headers

Shopsys Platform sets HTTP security headers at multiple layers to provide defense-in-depth protection.
This document describes each header, where it is set, and how it is configured.

## Architecture Overview

Security headers are applied at three layers:

| Layer                        | Scope                                                               | Configuration                                                                                                                                  |
| ---------------------------- | ------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| **nginx**                    | All responses (including static files and error pages)              | `project-base/docker/nginx/nginx.conf` (development), `project-base/app/orchestration/kubernetes/configmap/nginx.yaml` (production Kubernetes) |
| **PHP (Symfony)**            | Dynamic CSP on admin/backend main requests (excluding Frontend API) | `SecurityHeadersResponseListener`                                                                                                              |
| **Storefront (Next.js SSR)** | All storefront HTML pages                                           | `initServerSideProps.ts`                                                                                                                       |

### How headers reach each response type

Static security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `X-XSS-Protection`, `Strict-Transport-Security`, `X-Powered-By`) are set directly in nginx:

- server-level defaults
- explicitly redeclared in locations that define their own `add_header` directives

| Response type          | nginx location  | Inherits server-level headers?                    | Additional headers beyond server-level defaults                                                                                                                                  |
| ---------------------- | --------------- | ------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Admin pages            | `@app`          | No — has own `add_header`, so must redeclare them | PHP: CSP (dynamic); nginx `@app`: redeclared static security headers with stricter `Permissions-Policy` (`geolocation=()`); CORS headers intentionally not set                   |
| Storefront pages       | `@storefront`   | No — has own `add_header`, so must redeclare them | Next.js: CSP only (dynamic, obtained via GraphQL); nginx `@storefront` redeclares static security headers and intentionally does not expose CORS                                 |
| Frontend API (GraphQL) | `@app`          | No — same as admin                                | nginx `@app`: redeclared static security headers with stricter `Permissions-Policy` (`geolocation=()`); CSP is **not** set for API responses; CORS headers intentionally not set |
| Static files           | `try_files`     | Yes                                               | Inherit server-level CORS defaults (`Access-Control-Allow-Origin: *`, `Access-Control-Allow-Credentials: false`)                                                                 |
| Image resizer          | `@imageResizer` | No — has own `add_header`, so must redeclare them | nginx `@imageResizer`: app-style static security headers (`geolocation=()`), CORS intentionally not set                                                                          |

Important nginx rule: if a location block defines _any_ `add_header` directive, it does **not** inherit server-level `add_header` directives.
This is why `@app`, `@storefront`, and `@imageResizer` explicitly redeclare the security headers.
Each location then sets its own `Permissions-Policy` value where needed.
The storefront Next.js app only sets CSP because it's the only header that requires application-level logic (the value is configurable via admin and fetched from the database through a GraphQL query).

## Headers Reference

### Content-Security-Policy

**In plain English:** A whitelist that tells the browser "only load scripts, styles, images, and other resources from these trusted sources." If an attacker injects malicious code into your page, the browser will refuse to run it because it's not on the whitelist. This is the primary defense against XSS and content injection attacks.

| Property          | Value                                                                                                                                                                                                                                                       |
| ----------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Default value** | `frame-ancestors 'self'; default-src 'self' https: 'unsafe-inline' data:; script-src 'self' 'unsafe-inline' https://www.googletagmanager.com *.usersnap.com https://widget.packeta.com https://cdnjs.cloudflare.com https://*.gopay.com https://*.gopay.cz` |
| **Set by**        | PHP `SecurityHeadersResponseListener` (admin), Storefront `initServerSideProps` (storefront pages)                                                                                                                                                          |
| **Not set on**    | Frontend API (GraphQL) responses, static files                                                                                                                                                                                                              |
| **Configurable**  | Yes, via Admin > Superadmin > CSP Header Setting                                                                                                                                                                                                            |

**Default policy breakdown:**

| Directive         | Value                                                                                                                                                                   | Purpose                                                                                               |
| ----------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| `frame-ancestors` | `'self'`                                                                                                                                                                | Only allows embedding from the same origin (modern CSP replacement for X-Frame-Options)               |
| `default-src`     | `'self' https: 'unsafe-inline' data:`                                                                                                                                   | Fallback for resource types without a dedicated directive (keeps current non-script behavior)         |
| `script-src`      | `'self' 'unsafe-inline' https://www.googletagmanager.com *.usersnap.com https://widget.packeta.com https://cdnjs.cloudflare.com https://*.gopay.com https://*.gopay.cz` | Restricts script execution to same-origin and known third-party script providers used by the platform |

When default `cspHeader` is seeded by migration `Version20260208091423`, `CDN_DOMAIN` (if configured) is appended to both `default-src` and `script-src`.

**Development additions to CSP:**

| Context             | Directive     | Added source/value            | Purpose                                                                               |
| ------------------- | ------------- | ----------------------------- | ------------------------------------------------------------------------------------- |
| Storefront (dev)    | `script-src`  | `'unsafe-eval'`               | Required by webpack dev tooling / Next.js dev mode                                    |
| Backend admin (dev) | `script-src`  | `http://localhost:35729`      | LiveReload in admin                                                                   |
| Backend admin (dev) | `script-src`  | `http://cdnjs.cloudflare.com` | Allows protocol-relative elFinder script URLs resolved to `http://` on local HTTP     |
| Backend admin (dev) | `default-src` | `http://cdnjs.cloudflare.com` | Allows protocol-relative elFinder stylesheet URLs resolved to `http://` on local HTTP |

**Sanitization:**

The CSP value is sanitized when saved in Admin (carriage return `\r` and line feed `\n` are normalized to spaces) to prevent HTTP response splitting attacks.

### X-Frame-Options

**In plain English:** Prevents other websites from putting your pages inside a hidden frame. Without this, an attacker could overlay your admin or checkout page with their own site and trick users into clicking your buttons without realizing it (clickjacking).

| Property   | Value                                                                                          |
| ---------- | ---------------------------------------------------------------------------------------------- |
| **Value**  | `SAMEORIGIN`                                                                                   |
| **Set by** | nginx (`always` flag — server level; redeclared in `@storefront`, `@app`, and `@imageResizer`) |

`SAMEORIGIN` allows embedding only from the same origin, which is needed for admin features like CKEditor and elFinder. The default CSP also includes `frame-ancestors 'self'` as the modern replacement. Both are set for defense-in-depth — `X-Frame-Options` covers older browsers, static files, and API responses where CSP is not present.

### X-Content-Type-Options

**In plain English:** Tells the browser "trust the file type I'm telling you, don't try to guess." Without this, a browser might look at a file labeled as an image, decide it looks like JavaScript, and execute it — which an attacker could exploit by uploading a malicious file disguised as an image. Particularly important for e-commerce platforms that accept user-uploaded content.

| Property   | Value                                                                                          |
| ---------- | ---------------------------------------------------------------------------------------------- |
| **Value**  | `nosniff`                                                                                      |
| **Set by** | nginx (`always` flag — server level; redeclared in `@storefront`, `@app`, and `@imageResizer`) |

### Referrer-Policy

**In plain English:** Controls what URL information is shared when a user clicks a link to another site. With `strict-origin-when-cross-origin`, same-origin requests still receive the full referrer, while cross-origin HTTPS requests receive only the origin (for example, `https://shop.example`) without path/query details.

| Property   | Value                                                                                          |
| ---------- | ---------------------------------------------------------------------------------------------- |
| **Value**  | `strict-origin-when-cross-origin`                                                              |
| **Set by** | nginx (`always` flag — server level; redeclared in `@storefront`, `@app`, and `@imageResizer`) |

This is a balanced default: it preserves useful origin-level referrer data for embedded third-party integrations while avoiding leakage of sensitive URL path/query data.

### Strict-Transport-Security (HSTS)

**In plain English:** Tells the browser "from now on, only connect to this site over HTTPS — even if the user types `http://`." Once a browser sees this header, it will automatically upgrade all future requests to HTTPS for the specified duration. This prevents man-in-the-middle attacks where an attacker intercepts the initial insecure HTTP request before the server can redirect to HTTPS.

| Property   | Value                                                                                          |
| ---------- | ---------------------------------------------------------------------------------------------- |
| **Value**  | `max-age=31536000; includeSubDomains`                                                          |
| **Set by** | nginx (`always` flag — server level; redeclared in `@storefront`, `@app`, and `@imageResizer`) |

- `max-age=31536000` — the browser remembers the HTTPS-only policy for 1 year (in seconds)
- `includeSubDomains` — the policy also applies to all subdomains, preventing attacks on `api.example.com` or `cdn.example.com`

Note: browsers only honor this header when received over a valid HTTPS connection. In local development over HTTP, the header is present but ignored — so it won't interfere with the development setup.

Limitation: without the `preload` directive and HSTS preload list registration, a very first HTTP visit can still be vulnerable to SSL stripping before the browser learns the HSTS policy.

For highly security-oriented projects, consider enabling HSTS preload (`...; preload`) and registering the domain via `https://hstspreload.org/` (only after validating all preload requirements for the root domain and subdomains).

### X-XSS-Protection

**In plain English:** Older browsers had a built-in XSS filter (XSS Auditor) that tried to detect and block attacks, but it was unreliable and could actually be exploited by attackers to break legitimate pages. Setting this to `0` turns it off. Modern browsers have removed it entirely (Chrome 78+, Edge 79+, Firefox) — CSP is the proper replacement.

| Property   | Value                                                                                          |
| ---------- | ---------------------------------------------------------------------------------------------- |
| **Value**  | `0`                                                                                            |
| **Set by** | nginx (`always` flag — server level; redeclared in `@storefront`, `@app`, and `@imageResizer`) |

### X-Powered-By

**In plain English:** Upstreams (PHP/Next.js) may expose technology/version details in `X-Powered-By`. Attackers use this to target known vulnerabilities. We overwrite it with a generic `Shopsys Platform` value so no useful stack/version information is exposed.

| Property   | Value                                                                                                                                                                                          |
| ---------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Value**  | `Shopsys Platform`                                                                                                                                                                             |
| **Set by** | nginx (server-level `add_header`; redeclared in `@storefront`, `@app`, and `@imageResizer` because location-level `add_header` disables inheritance; `@storefront` also hides upstream header) |

### Permissions-Policy

**In plain English:** Controls access to browser features such as camera, microphone, geolocation, payment APIs, and USB. Most features are disabled for all origins using `()`. Geolocation is allowed only for same-origin storefront responses via server-level policy, while `@app` responses (admin/GraphQL/PHP) override it to `geolocation=()`.

| Property   | Value                                                                                                                                                                                                                              |
| ---------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Value**  | server-level default: `camera=(), geolocation=(self), microphone=(), payment=(), usb=()`; `@storefront` redeclares same value; `@app`/`@imageResizer`/PHP override: `camera=(), geolocation=(), microphone=(), payment=(), usb=()` |
| **Set by** | nginx (server-level `add_header`; `@storefront`, `@app`, and `@imageResizer` redeclare headers; only `@storefront` keeps `geolocation=(self)`)                                                                                     |

### Access-Control-Allow-Origin (CORS)

**In plain English:** By default, browsers block JavaScript on `site-a.com` from fetching resources from `site-b.com`. This header says "it's OK, these origins are allowed to load my resources." The wildcard `*` means "anyone can load this" — used here only for public static assets. For dynamic routes (storefront HTML, admin, GraphQL, PHP endpoints) it is removed.

| Property   | Value                                                                                                                              |
| ---------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| **Value**  | `*` by default (nginx server level on responses that inherit it), no header on `@storefront`, `@app`, or `@imageResizer` responses |
| **Set by** | nginx                                                                                                                              |

Static `content` locations that proxy to object storage strip upstream CORS via `proxy_hide_header`, so only nginx-controlled values are sent.  
`@storefront` strips CORS from the Next.js upstream and defines its own `add_header` block, so server-level CORS is not inherited.  
`@app` and `@imageResizer` do the same for FastCGI responses (`fastcgi_hide_header` + location-level headers).

**Why CORS is intentionally disabled on `@app`:**

- In production, storefront and GraphQL/admin are served under the same origin, so CORS is not needed for normal browser traffic.
- `@app` serves sensitive backend endpoints (GraphQL, admin, authenticated PHP routes); omitting CORS headers prevents accidental cross-origin access.
- Keeping CORS off on `@app` also avoids conflicting behavior when multiple layers (application, nginx, CDN/proxy) try to set CORS headers.

### Access-Control-Allow-Credentials

**In plain English:** Even when cross-origin requests are allowed (via `Access-Control-Allow-Origin`), this header controls whether the browser sends cookies or login tokens along with those requests. Set to `false` — so an external site can load a public image, but cannot make requests as a logged-in user. Browsers also enforce this: when `Access-Control-Allow-Origin` is `*`, credentials are always blocked regardless.

| Property   | Value                                                                                                                                  |
| ---------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| **Value**  | `false` by default (nginx server level on responses that inherit it), no header on `@storefront`, `@app`, or `@imageResizer` responses |
| **Set by** | nginx                                                                                                                                  |

### Additional nginx security settings

**In plain English:** nginx normally includes its version number in every response (e.g., `Server: nginx/1.25.3`). Turning `server_tokens` off removes the version, so attackers can't scan for servers running vulnerable nginx versions. Same idea as X-Powered-By above.

| Setting         | Value |
| --------------- | ----- |
| `server_tokens` | `off` |

## Configuring CSP via Admin

The Content-Security-Policy header value can be configured by a **superadmin** in the administration interface:

**Admin > Superadmin > CSP Header Setting**

The value is stored as a global setting (not per-domain) and is applied to:

- All admin/backend pages (via PHP `SecurityHeadersResponseListener`)
- All storefront pages (via GraphQL `Settings.cspHeader` query, applied in `initServerSideProps`)

After saving a new CSP value, the storefront GraphQL query cache is automatically cleared to ensure the new policy takes effect immediately.

## CSP Value Flow

```
1. Superadmin saves CSP value in Admin UI
   |
2. Value is sanitized (`\r`, `\n` normalized to spaces) and stored in DB (setting_values table)
   |
   +--> 3a. PHP SecurityHeadersResponseListener reads from DB
   |         and sets Content-Security-Policy header on admin responses
   |
   +--> 3b. GraphQL Settings query exposes cspHeader field
             |
             4. Storefront initServerSideProps reads from Settings query
                and sets Content-Security-Policy header on storefront responses
```
