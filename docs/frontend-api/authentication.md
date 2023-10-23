# Authentication

In a modern web application (or native mobile application), you’ll likely need to make parts of your app private so they are not accessible by anybody.
If you want some users to be able to access those restricted parts, you need to implement some sort of user authentication in your app, so that users can create an account, and later log into that account to access protected content.
User authentication in Shopsys Platform Frontend API uses access tokens.

Access Tokens are used in token-based authentication to allow an application to access an API. For example, a customer orders API methods can read orders for only one (authorized) customer.
Once an application has received an Access Token, it will include that token as a credential during API requests. To do so, it should transmit the Access Token to the API as a Bearer credential in an HTTP Authorization header.

[JWT](https://jwt.io/introduction/) tokens are used as access tokens in Shopsys Platform.

You can read more about access tokens in [this article](https://auth0.com/docs/tokens/concepts/access-tokens).

The access token has a short lifetime (15 minutes by default).
In order to not force users to log in every 15 minutes, we have implemented refresh tokens that have a longer lifetime (14 days in the base).
You can read more about refresh tokens [here](https://auth0.com/docs/tokens/concepts/refresh-tokens).

Tokens are signed with a private key and verified with its public key in Shopsys Platform.
You can generate them using the command `./phing frontend-api-generate-new-keys`.
If these keys leak, you can generate a new key pair with the same command.
Note that when you regenerate the keys, you invalidate all issued access and refresh tokens.

## How to get a pair of tokens

To get a pair of tokens, you can use the mutation query `Login` using the user's email and password.
The query will return access and refresh tokens.

`Login` query looks like this:

```json
{ "query": "mutation {Login(input: {email: \"--EMAIL--\", password: \"--PASSWD--\"}) {accessToken, refreshToken}}"}
```

!!!
You should always send queries over a secure HTTPS protocol.

The response looks like this:

```json
{
  "data":{
    "Login":{
      "accessToken":"X.X.X",
      "refreshToken":"X.X.X"
    }
  }
}
```

The access token carries basic user information such as its UUID, name, email and role list.

If you're running an application in [Docker](../installation/installation-using-docker-application-setup.md) and you have [allowed frontend API on the first domain](./introduction-to-frontend-api.md), you can run a curl command to get pair of tokens like this:

```sh
  curl
    -X POST
    -H "Content-Type: application/json"
    -d '{"query": "mutation {Login(input: {email: \"--EMAIL--\", password: \"--PASSWD--\"}) {accessToken, refreshToken}}"}'
    http://127.0.0.1:8000/graphql/
```

## How to access the restricted parts

If you need to access the protected section, you must send a Bearer credential in the Authorization header.

The Authorization header looks like this:

```sh
  curl
    -H "Authorization: Bearer --ACCESS TOKEN--"
```

The user is authenticated by the `TokenAuthenticator` class.
This authenticator creates (if the token is valid) an instance of the user represented by the `FrontendApiUser` class.
This instance contains only the user information that is part of the access token.
This is because the authorized API query cannot create a database query to get more detailed user information.
If the resolver needs more detailed information, it has to obtain it himself.

## How to renew access and refresh token

If the user is successfully logged in and the access token expires, the 401 error will be returned. In such a case, the user should use the refresh token to restore the access token.

!!!
    We do not recommend refreshing the access token before each request, because token renewal takes some time and the user will have to wait a long time for a response.

To refresh the pair of tokens, use the `RefreshTokens` mutation query with the `refreshTokenInput` input parameter.

`RefreshToken` query looks like this:


```json
{ "query": "mutation {RefreshTokens(input: {refreshToken: \"X.X.X\"}) {accessToken, refreshToken}}"}
```

The response is the same as Login query:

```json
{
  "data":{
    "Login":{
      "accessToken":"X.X.X",
      "refreshToken":"X.X.X"
    }
  }
}
```

If you're running an application in [Docker](../installation/installation-using-docker-application-setup.md) and you have [allowed frontend API on first domain](./introduction-to-frontend-api.md), curl command to renewal pair of tokens may look like this:

```sh
  curl
    -X POST
    -H "Content-Type: application/json"
    -d '{"query": "mutation {RefreshTokens(input: {refreshToken: \"X.X.X\"}) {accessToken, refreshToken}}"}'
    http://127.0.0.1:8000/graphql/
```
