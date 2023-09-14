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
Responsible for adding access token as `Authorization: Bearer xxx` header to each request made with URQL.
The authorization header is not added when no `authState` exists (no previously authenticated user) or when the `RefreshTokens` mutation is performed.
When the `RefreshToken` mutation is performed, the access token can be already invalid, and FE API blocks every request with an invalid access token.

`didAuthError`
Check whether an error returned from the API is an authentication error (e.g. HTTP response status code is 401).

`refreshAuth`
This function parses the refresh token from the token cookie and tries to refresh the access token. It uses `setTokensToCookies` to both refresh them and store them in a cookie. In the case of a successful refresh, tokens are immediately available for subsequent requests.

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
`accessToken` and `refreshToken` are then stored in the cookie, and the Zustand state is updated with information that the user is logged in.

User logout

```ts
const [, [, logout]] = useAuth();

logout();
```

Zustand state is updated with information that the user is no longer authenticated, and tokens are deleted from the cookie.
