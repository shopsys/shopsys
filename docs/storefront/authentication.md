# Authentication

## Frontend API

If you didn't do it previously, you have to generate private keys for the Frontend API.

```bash
php phing frontend-generate-new-keys
```

## Authentication mechanism

Authentication is performed via [@urql/auth-exchange](https://formidable.com/open-source/urql/docs/advanced/authentication).
Proper options for authExchange can be obtained with `getAuthExchangeOptions` from `urql/authExchange.ts` and it can be used for server-side rendering and client side requests.

Following options are created for `authExchange`:

`addAuthToOperation`
Responsible for adding access token as `Authorization: Bearer xxx` header to each request made with Urql.
Authorization header is not added when no authState exists (no previously authenticated user) or when `RefreshTokens` mutation is performed.
Because when the `RefreshToken` mutation is performed, the access token can be already invalid and FE API blocks every request with invalid access token.

`didAuthError`
Check whether error returned from the API is an authentication error (e.g. HTTP response status code is 401).

`getAuth`
This option is created with factory `createGetAuth`, so it's possible to pass `GetServerSidePropsContext` which is necessary for properly authenticated requests in SSR.
Initially, when no `authState` is stored in memory, tokens are loaded from persistent storage (cookies).
When `refreshToken` does not exist in persistent storage, request is then handled as anonymous.
When `accessToken` does not exist in persistent storage, a refresh token attempt is made immediately.
`getAuth` function is also invoked after the `didAuthError` function returns `true` (after authentication error in any request).
In that case `authState` is already present in memory and a refresh token attempt is made immediately.

While the urql is refreshing tokens, all other calls are paused.
After successful refresh, previously forbidden requests are re-executed with the new access token.

For logging the user in/out we can use `useAuth` hook.

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
`accessToken` and `refreshToken` are then stored in the cookie and the Zustand state is updated with information the user is logged in.

User logout

```ts
const [, [, logout]] = useAuth();

logout();
```

Zustand state is updated with information the user is no longer authenticated and tokens are deleted from the cookie.
