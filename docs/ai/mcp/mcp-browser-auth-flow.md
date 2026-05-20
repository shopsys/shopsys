# MCP browser authentication flow

This document describes the current browser-based OAuth authentication flow for the Shopsys MCP server.

The flow has been tested with Claude Code, but it is intended to work with any AI client that supports OAuth-based login for remote MCP servers.

It is not a generic OAuth guide. It follows the current Shopsys MCP integration points and route contracts.

## High-level overview

When an AI client starts authentication for the configured MCP server, the flow is:

1. the client discovers the OAuth metadata
2. the client dynamically registers itself
3. it opens the Shopsys browser authorization page
4. the administrator allows or denies access
5. the client exchanges the authorization code for an MCP bearer token
6. the client uses that bearer token for MCP requests

## Step-by-step

### 1. Authorization server metadata

- Route name: `mcp_oauth_metadata`
- Request: `GET`
- Response: `issuer`, `authorization_endpoint`, `token_endpoint`, `registration_endpoint`, and the supported response type, grant type, auth method, and PKCE method
- Note: thanks to this endpoint, the AI client can discover the remaining OAuth endpoints from metadata instead of hardcoding them in advance.

### 2. Dynamic client registration

- Route name: `mcp_oauth_register`
- Request: `POST` JSON payload with `redirect_uris` and optional `client_name`
- Response: generated `client_id`, stored `client_name`, and accepted `redirect_uris`
- Note: the registration is temporary and supports the later authorization and token exchange.
- Rate limit: the endpoint is rate-limited by client IP.

### 3. Browser authorization request

- Route name: `admin_superadmin_mcp_oauth_authorize`
- Request: `GET` query with `response_type=code`, `client_id`, `redirect_uri`, `state`, `code_challenge`, and `code_challenge_method=S256`
- Behavior: Shopsys validates the request shape and the registered `client_id` / `redirect_uri`, then shows the consent page to the administrator.

### 4. Consent result

- Route name: `admin_superadmin_mcp_oauth_authorize`
- Request: `POST` submit of either allow or deny
- Deny redirect: `error=access_denied` and `state`
- Allow redirect: `code` and `state`
- Note: the issued authorization code is short-lived and one-time use.

### 5. Token exchange

- Route name: `mcp_oauth_token`
- Request: `POST` form payload with `grant_type=authorization_code`, `client_id`, `redirect_uri`, `code`, and `code_verifier`
- Validation: registered client exists, `redirect_uri` matches the client registration, authorization code exists and is not expired, authorization code matches `client_id`, authorization code matches `redirect_uri`, and the PKCE verifier matches the stored challenge
- Response: `access_token`, `token_type=Bearer`, and `expires_in`
- Note: the connected-client token is stored for the concrete OAuth `client_id` with token type `oauth` and a readable label derived from `client_name`.
- Rate limit: the endpoint is rate-limited by client IP.

### 6. Authenticated MCP requests

- Route name: `_mcp_endpoint`
- Request: HTTP MCP request with `Authorization: Bearer <access_token>`
- Behavior: the token is resolved to an administrator, token usage is recorded, and MCP tool access is granted for the authenticated administrator.
- Rate limit: runtime MCP requests always consume an IP-based limit and also consume a token-public-ID limit when the bearer token has the generated token shape.

## State used during the flow

Temporary state:

- client registrations are stored in cache
- authorization codes are stored in cache

Persistent state:

- connected-client MCP access tokens are stored in the database

## Practical note about repeated authorization

In practice, an AI client may reuse or recreate its OAuth client registration depending on how it scopes the MCP server configuration.

For example:

- one project may reuse the same OAuth `client_id`
- another project may register a different OAuth `client_id`

That is why the administration can show multiple connected-client tokens with the same human-readable `client_name` but different technical `client_id` values.

Manual tokens are separate from this OAuth flow. They can be generated multiple times, have their own labels and expirations, and appear in the same administration overview as OAuth-issued tokens.
