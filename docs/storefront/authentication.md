# Authentication

## Frontend API

If you didn't do it previously, you have to generate private keys for the Frontend API.

```bash
php phing frontend-generate-new-keys
```

## Authentication mechanism

Authentication is performed via [@urql/auth-exchange](https://formidable.com/open-source/urql/docs/advanced/authentication).
Proper options for authExchange can be obtained with `getAuthExchangeOptions` from `urql/authExchange.ts` and it can be used for server-side rendering and client-side requests.

The following options are created for `authExchange`:

`addAuthToOperation`
Responsible for adding access token as `X-Auth-Token: Bearer xxx` header to each request made with URQL.
The authentication header is not added when no access token exists or when the `RefreshTokens` mutation is performed.
When the `RefreshToken` mutation is performed, the access token can be already invalid, and FE API blocks every request with an invalid access token.

`didAuthError`
Check whether an error returned from the API is an authentication error (e.g. HTTP response status code is 401).

`refreshAuth`
On the server, this function reads the refresh token from the request's `HttpOnly` cookie and calls the Frontend API directly.
In the browser, it calls the allowlisted `/api/auth/token` endpoint, which reads and rotates the refresh token server-side.
The refresh token is never exposed to client-side JavaScript or sent directly from the browser to the Frontend API.
In the case of a successful refresh, the endpoint stores the rotated tokens in cookies and the new access token is immediately available for subsequent requests.

While the URQL is refreshing tokens, all other calls are paused.
After a successful refresh, previously forbidden requests are re-executed with the new access token.

### Refresh coordination between tabs

Browser tabs on the same origin coordinate token refresh with the [Web Locks API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Locks_API).
Each domain ID has its own exclusive lock, so language domains sharing an origin but using separate cookies do not block each other.
After acquiring the lock, a tab rereads the access-token cookie. If another tab has already replaced the token, the waiting tab uses that token without sending another `RefreshTokens` mutation.
The refresh-session cookie is also checked inside the lock, so a waiting tab observes logout or a failed refresh in another tab.

Web Locks requires a secure context (HTTPS or a trustworthy local origin) and is supported by Chromium browsers since version 69, Firefox since version 96, and Safari since version 15.4.
It adds no dependencies. The browser releases the lock when the callback finishes, rejects, or its tab closes.
Waiting for a lock and performing a refresh each have a ten-second timeout. A timed-out refresh aborts its browser request and releases the lock without removing cookies or reloading the page because of a late result.
If waiting for a lock times out (for example, because the holder is suspended), queued requests fail with a network error and can be retried later. They do not bypass the held lock, because it may protect a logout.
If Web Locks is unavailable or rejects a lock request before acquiring it, the tab falls back to an independent refresh after rereading the cookie.
In that fallback, concurrent requests remain possible; server-side refresh-token grace handling is still needed.

[BroadcastChannel](https://developer.mozilla.org/en-US/docs/Web/API/Broadcast_Channel_API) was considered, but messaging alone does not provide mutual exclusion or automatic recovery when a lock holder closes.
A `localStorage` flag also lacks atomic compare-and-set and would require expiry and ownership recovery.
Web Locks supplies these ownership guarantees directly. Coordination applies to browser tabs on the same origin and storage partition; SSR requests continue to refresh independently.
Client coordination cannot recover a newly rotated token pair if the response carrying it is lost.

### Logging in and out

For logging the user in/out, we can use the `useAuth` hook.

```plain
/hooks/auth/useAuth.tsx
```

User login

```ts
const [[loginResult, login]] = useAuth();

login(email: string, password: string);
```

This function calls the API mutation with the provided email and password.
If everything is OK, the user is logged in.
The access token is stored in a JavaScript-readable session cookie and the refresh token is stored in a `Secure`, `HttpOnly`, `SameSite=Lax` cookie.
A non-sensitive `refreshTokenPresent` cookie lets the client detect that a refresh session exists without exposing the token itself.
The Zustand state is then updated with information that the user is logged in.

User logout

```ts
const [, [, logout]] = useAuth();

logout();
```

Zustand state is updated with information that the user is no longer authenticated, and the server-side auth endpoint deletes all authentication cookies.

The browser logout request uses the same per-domain lock as refresh. It waits until an earlier refresh response has stored its cookies, then clears them with the logout response. A queued refresh rereads the cookies after logout and cannot restore the session.
Only the logout HTTP request acquires the lock: URQL may need to refresh the session before it can send that request. The logout request also has a ten-second timeout; its aborted request cannot later overwrite cookies.
As with refresh coordination, browsers without usable Web Locks and independent SSR requests do not share this browser lock.
