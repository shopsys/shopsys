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

| Item | Value |
| --- | --- |
| Route name | `mcp_oauth_metadata` |
| Request | `GET` |
| Response | `issuer`, `authorization_endpoint`, `token_endpoint`, `registration_endpoint`, and the supported response type, grant type, auth method, and PKCE method |
| Note | Thanks to this endpoint, the AI client can discover the remaining OAuth endpoints from metadata instead of hardcoding them in advance. |

### 2. Dynamic client registration

| Item | Value |
| --- | --- |
| Route name | `mcp_oauth_register` |
| Request | `POST` JSON payload with `redirect_uris` and optional `client_name` |
| Response | generated `client_id`, stored `client_name`, and accepted `redirect_uris` |
| Note | The registration is temporary and supports the later authorization and token exchange. |
| Rate limit | The endpoint is rate-limited by client IP. |

### 3. Browser authorization request

| Item | Value |
| --- | --- |
| Route name | `admin_superadmin_mcp_oauth_authorize` |
| Request | `GET` query with `response_type=code`, `client_id`, `redirect_uri`, `state`, `code_challenge`, and `code_challenge_method=S256` |
| Behavior | Shopsys validates the request shape and the registered `client_id` / `redirect_uri`, then shows the consent page to the administrator. |

### 4. Consent result

| Item | Value |
| --- | --- |
| Route name | `admin_superadmin_mcp_oauth_authorize` |
| Request | `POST` submit of either allow or deny |
| Deny redirect | `error=access_denied` and `state` |
| Allow redirect | `code` and `state` |
| Note | The issued authorization code is short-lived and one-time use. |

### 5. Token exchange

| Item | Value |
| --- | --- |
| Route name | `mcp_oauth_token` |
| Request | `POST` form payload with `grant_type=authorization_code`, `client_id`, `redirect_uri`, `code`, and `code_verifier` |
| Validation | registered client exists, `redirect_uri` matches the client registration, authorization code exists and is not expired, authorization code matches `client_id`, authorization code matches `redirect_uri`, and the PKCE verifier matches the stored challenge |
| Response | `access_token`, `token_type=Bearer`, and `expires_in` |
| Note | The connected-client token is stored for the concrete OAuth `client_id` together with the readable `client_name`. |
| Rate limit | The endpoint is rate-limited by client IP. |

### 6. Authenticated MCP requests

| Item | Value |
| --- | --- |
| Route name | `_mcp_endpoint` |
| Request | HTTP MCP request with `Authorization: Bearer <access_token>` |
| Behavior | The token is resolved to an administrator, token usage is recorded, and MCP tool access is granted for the authenticated administrator. |
| Rate limit | Runtime MCP requests always consume an IP-based limit and also consume a token-public-ID limit when the bearer token has the generated token shape. |

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
