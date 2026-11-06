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
Responsible for adding access token as `X-Auth-Token: Bearer xxx` header to each server-side request made with URQL.
The authentication header is not added when no access token exists or when the `RefreshTokens` mutation is performed.
When the `RefreshToken` mutation is performed, the access token can be already invalid, and FE API blocks every request with an invalid access token.
In the browser, no header is added: the access token lives in an `HttpOnly` cookie that the browser sends with the request itself, and the Frontend API accepts it only for same-origin `POST` requests carrying the `X-Domain-Id` header.

`didAuthError`
Check whether an error returned from the API is an authentication error (e.g. HTTP response status code is 401).

`refreshAuth`
On the server, this function reads the refresh token from the request's `HttpOnly` cookie and calls the Frontend API directly.
In the browser, it calls the allowlisted `/api/auth/token` endpoint, which reads and rotates the refresh token server-side.
The refresh token is never exposed to client-side JavaScript or sent directly from the browser to the Frontend API.
In the case of a successful refresh, the endpoint stores the rotated tokens in cookies and the new access token is immediately available for subsequent requests.

While the URQL is refreshing tokens, all other calls are paused.
After a successful refresh, previously forbidden requests are re-executed with the new access token.

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
Both tokens are stored in `Secure`, `HttpOnly`, `SameSite=Lax` cookies (the access token as a session cookie, the refresh token with a 14-day lifetime) and the auth endpoint returns them to the browser blanked.
A non-sensitive `refreshTokenPresent` cookie lets the client detect that a session exists without exposing the tokens themselves.
Whether an administrator is logged in as the customer is exposed by the `isAdministratorLoggedAsCustomer` field of `currentCustomerUser`, so the browser never needs to decode the token.
The Zustand state is then updated with information that the user is logged in.

User logout

```ts
const [, [, logout]] = useAuth();

logout();
```

Zustand state is updated with information that the user is no longer authenticated, and the server-side auth endpoint deletes all authentication cookies.
