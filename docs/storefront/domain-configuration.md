# Domain Configuration & Locale-in-Path Functionality

[TOC]

## Overview

The Shopsys storefront supports multi-domain configurations with flexible locale handling. Each domain can serve content for a specific locale, and domains can optionally include the locale in their URL path (e.g., `/sk/`). This documentation explains how domain configuration works, how cookies are isolated between domains, and how to work with locale-in-path domains.

## Domain Types

### 1. Standard Domains (No Locale in Path)

- **Examples**: `http://127.0.0.1:8000`, `http://127.0.0.2:8000`
- **URL Structure**: `/products`, `/categories`, `/cart`
- **Locale**: Determined by domain configuration (`defaultLocale`)
- **Cookie Naming**: Uses domain ID for isolation

### 2. Locale-in-Path Domains

- **Example**: `http://127.0.0.1:8000/sk`
- **URL Structure**: `/sk/products`, `/sk/categories`, `/sk/cart`
- **Locale**: Extracted from URL path segment
- **Cookie Naming**: Uses domain ID for isolation (same as standard domains)
- **Special Handling**: Middleware preserves locale prefix in all URLs

## Fallback Domain Redirection

The Shopsys storefront includes intelligent fallback domain redirection functionality that only activates when a user accesses a base URL that has no exact domain configuration. This is a fallback mechanism, not the primary domain resolution method.

### Primary Resolution: Exact Domain Matching

The system always attempts exact domain matching first. With the current configuration:

- **`http://127.0.0.1:8000/en`** matches domain 1 (`http://127.0.0.1:8000/`) → `/en` is treated as a regular path
- **`http://127.0.0.1:8000/anything`** matches domain 1 (`http://127.0.0.1:8000/`) → `/anything` is treated as a regular path
- **`http://127.0.0.2:8000/products`** matches domain 2 (`http://127.0.0.2:8000/`) → `/products` is the application path
- **`http://127.0.0.1:8000/sk/categories`** matches domain 3 (`http://127.0.0.1:8000/sk/`) → `/categories` is the application path

**Important**: Paths that look like locales are treated as regular application paths unless they match an exact domain configuration with that locale path.
However, paths matching locales configured in `i18n.js` (currently `['default', 'en', 'cs', 'sk']`) will be intercepted by Next.js i18n system before reaching domain resolution. In the current setup, `/fr` etc. on `127.0.0.2:8000` are regular paths served by domain 2, while `/en`, `/cs`, `/sk` paths are handled by Next.js i18n on any domain.

### When Fallback Redirection Is Triggered

Fallback redirection would only be triggered in configurations where you have "orphaned" base URLs. For example, if domains were configured like this:

```
- http://127.0.0.1:8000/cs/ (Czech, domain 1)
- http://127.0.0.1:8000/sk/ (Slovak, domain 2)
Note: No domain configured for base http://127.0.0.1:8000/

Then accessing http://127.0.0.1:8000/products would trigger fallback:
- Browser: Accept-Language: sk-SK,sk;q=0.9,en;q=0.8
- Result: Redirects to http://127.0.0.1:8000/sk/products (matches browser locale)
```

### Browser Locale Detection

The system parses the `Accept-Language` header to extract user preferences:

- **Quality values**: Respects `q` parameters (e.g., `sk;q=0.9`)
- **Language extraction**: Extracts base language codes (e.g., `cs` from `cs-CZ`)
- **Priority ordering**: Processes locales in order of preference
- **Fallback behavior**: Uses first available domain if no locale matches

### Technical Implementation

The redirection logic is implemented in:

- **`getHostAndDomainFromRequest`**: Domain resolution with redirect flag
- **`middleware.ts`**: HTTP redirect handling with 308 status code
- **Browser locale parsing**: Automatic `Accept-Language` header processing

## Core Configuration

### Domain Configuration Type

Each domain is configured with the following properties:

```typescript
export type DomainConfigType = {
    url: string; // Full domain URL (may include locale path)
    publicGraphqlEndpoint: string; // GraphQL API endpoint
    defaultLocale: string; // Locale code (en, cs, sk, etc.)
    currencyCode: string; // Currency for the domain
    fallbackTimezone: string; // Default timezone
    domainId: number; // Unique domain identifier
    mapSetting: {
        // Map display settings
        latitude: number;
        longitude: number;
        zoom: number;
    };
    gtmId?: string; // Google Tag Manager ID
    isLuigisBoxActive: boolean; // Luigi's Box integration
    type: CustomerUserAreaEnum; // B2B or B2C
};
```

### Configuration Example

```javascript
// next.config.js
publicRuntimeConfig: {
    domains: [
        {
            url: 'http://127.0.0.1:8000',
            defaultLocale: 'en',
            domainId: 1,
            currencyCode: 'EUR',
            // ... other config
        },
        {
            url: 'http://127.0.0.2:8000', // Base domain without locale
            defaultLocale: 'cs',
            domainId: 2,
            currencyCode: 'CZK',
            // ... other config
        },
        {
            url: 'http://127.0.0.1:8000/sk', // Locale in path
            defaultLocale: 'sk',
            domainId: 3,
            currencyCode: 'EUR',
            // ... other config
        },
    ];
}
```

## Cookie Isolation Strategy

### The Challenge

When multiple domains share the same host (e.g., `127.0.0.1:8000` and `127.0.0.1:8000/sk`), they need isolated cookies to prevent:

- Authentication conflicts (users logged into wrong domain)
- Cart data mixing between domains
- User preferences bleeding across domains

### The Solution: Name-Based Cookie Isolation

All cookies use `path="/"` with domain-specific naming based on the domain ID:

```typescript
// utils/cookies/cookieNaming.ts
export const getCookieName = (baseName: string, domainId: number): string => {
    // All domains append their ID for consistency
    return `${baseName}-${domainId}`;
};
```

### Cookie Naming Examples

| Domain URL                 | Domain ID | Access Token    | Refresh Token    | Cookie Store     |
| -------------------------- | --------- | --------------- | ---------------- | ---------------- |
| `http://127.0.0.1:8000`    | 1         | `accessToken-1` | `refreshToken-1` | `cookiesStore-1` |
| `http://127.0.0.2:8000`    | 2         | `accessToken-2` | `refreshToken-2` | `cookiesStore-2` |
| `http://127.0.0.1:8000/sk` | 3         | `accessToken-3` | `refreshToken-3` | `cookiesStore-3` |

### Why Name-Based Instead of Path-Based?

Initially, we tried using cookie paths (`/` vs `/sk/`) for isolation, but this caused issues:

- Cookies with `path="/sk/"` became temporarily invisible during page reloads
- This created race conditions where users appeared logged out momentarily
- Browser security correctly hides path-scoped cookies when path context is lost

Name-based isolation with `path="/"` solves all these issues:

- Cookies are always visible regardless of navigation state
- No race conditions during page reloads
- Simpler and more reliable implementation
- Complete domain isolation maintained through naming

## Complete Domain Isolation System

Beyond cookies, the Shopsys storefront implements complete domain isolation across all persistent storage mechanisms:

### 1. Cookie Isolation (Authentication & Settings)

- Uses domain-specific naming: `accessToken-3`, `refreshToken-3`, `refreshTokenPresent-3`, `cookiesStore-3`
- All cookies use `path="/"` for reliable visibility
- Refresh tokens are stored in `Secure`, `HttpOnly`, `SameSite=Lax` cookies and are only read server-side
- Prevents authentication and preference conflicts

### 2. LocalStorage Isolation (Persist Store)

- **Domain-specific store names**: Each domain gets its own localStorage key
- **Store naming pattern**: `persist-store-${domainId}` (e.g., `persist-store-3`)
- **Isolated data**: Shopping carts, wishlists, user preferences, contact information
- **Cross-tab sync**: Each domain has its own broadcast channel for tab synchronization

### 3. Broadcast Channel Isolation

- **Domain-specific channels**: `reloadPage_1`, `reloadPage_2`, `reloadPage_3`
- **Prevents cross-domain interference**: Tabs from different domains don't affect each other
- **Maintains sync within domain**: All tabs for the same domain stay synchronized

### Architecture Overview

```typescript
// Domain 1 (127.0.0.1:8000)
- Cookies: accessToken-1, refreshToken-1, cookiesStore-1
- LocalStorage: persist-store-1
- Broadcast: reloadPage_1

// Domain 2 (127.0.0.2:8000)
- Cookies: accessToken-2, refreshToken-2, cookiesStore-2
- LocalStorage: persist-store-2
- Broadcast: reloadPage_2

// Domain 3 (127.0.0.1:8000/sk)
- Cookies: accessToken-3, refreshToken-3, cookiesStore-3
- LocalStorage: persist-store-3
- Broadcast: reloadPage_3
```

## Working with Cookies

### Setting Authentication Cookies

```typescript
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';

// Server-side only
const domainConfig = getDomainConfig(context);
setTokensToCookies(accessToken, refreshToken, domainConfig, context);
```

Browser authentication mutations use the `/api/auth/token` endpoint, which sets authentication cookies server-side.

### Reading Authentication Cookies

```typescript
import {
    getAccessTokenFromCookies,
    getRefreshTokenFromCookies,
    hasRefreshTokenInCookies,
} from 'utils/auth/getTokensFromCookies';

// Client-side
const domainConfig = useDomainConfig();
const accessToken = getAccessTokenFromCookies(domainConfig);
const hasRefreshSession = hasRefreshTokenInCookies(domainConfig);

// Server-side
const domainConfig = getDomainConfig(context);
const refreshToken = getRefreshTokenFromCookies(domainConfig, context);
```

Client-side code must never read or send the refresh token directly.

### Removing Cookies

```typescript
import { clearAuthCookies } from 'utils/auth/authMutationFetcher';

// Clear auth cookies for current domain
await clearAuthCookies(domainConfig);
```

## Working with Persist Store

The persist store handles shopping carts, wishlists, user preferences, and other client-side data that needs to persist across browser sessions.

### Automatic Domain Isolation

The persist store is automatically isolated per domain through the `PersistStoreProvider`. The provider internally uses the domain configuration to create domain-specific stores:

```typescript
// PersistStoreProvider automatically isolates storage per domain
<PersistStoreProvider>
    <YourApp />
</PersistStoreProvider>

// Internal implementation creates domain-specific store names:
// createPersistStore(domainConfig.domainId) -> "shopsys-platform-persist-store-3"
```

### Using the Persist Store

```typescript
import { usePersistStore } from 'store/usePersistStore';

// All persist store operations are automatically domain-specific
const MyComponent = () => {
    const cartUuid = usePersistStore((state) => state.cartUuid);
    const updateCartUuid = usePersistStore((state) => state.updateCartUuid);
    const productLists = usePersistStore((state) => state.productListUuids);

    // These operations only affect the current domain's data
    updateCartUuid('new-cart-id');
};
```

### What's Stored Per Domain

- **Shopping Cart**: Cart UUID and items
- **Product Lists**: Wishlists and comparison lists
- **User Preferences**: Contact information, consent settings
- **Authentication State**: Loading states and user entry tracking

## Working with Broadcast Channels

Broadcast channels synchronize state between multiple browser tabs for the same domain.

### Domain-Specific Broadcasting

```typescript
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';

const MyComponent = () => {
    const domainConfig = useDomainConfig();

    const notifyOtherTabs = () => {
        // Only tabs from the same domain will receive this
        dispatchBroadcastChannel('reloadPage', domainConfig.domainId);
    };
};
```

### Broadcast Channel Events

- **`reloadPage`**: Triggers page reload in other tabs (used after login/logout)
- **Store Updates**: Automatic synchronization of persist store changes
- **Custom Events**: Can be extended for domain-specific communication

### Benefits of Domain-Specific Channels

- **No Cross-Domain Interference**: Logging out of domain 2 won't affect domain 3 tabs
- **Proper Isolation**: Each domain maintains independent state across tabs
- **Performance**: Reduced unnecessary updates from unrelated domains

## Domain Utility Functions

### Extracting Locale from Domain URL

```typescript
import { getExplicitPathDomainLocaleOrDefault } from 'utils/domain/domainUtils';

// Extract locale or return default
getExplicitPathDomainLocaleOrDefault('http://127.0.0.1:8000/sk'); // 'sk'
getExplicitPathDomainLocaleOrDefault('http://127.0.0.1:8000'); // 'default'
```

### Getting Domain Configuration

```typescript
// Client-side: Use React context hook
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
const domainConfig = useDomainConfig();

// Server-side: Extract from Next.js context
import { getDomainConfig } from 'utils/domain/domainConfig';
const domainConfig = getDomainConfig(context);
```

## Middleware and Routing

### How Locale-in-Path Works

The Next.js middleware (`middleware.ts`) handles locale-in-path domains by:

1. **Extracting locale from request**: Parses the URL to identify domain and locale
2. **Rewriting URLs**: Maps public URLs to internal Next.js pages
3. **Preserving locale context**: Ensures all redirects maintain the locale prefix

Example flow for `http://127.0.0.1:8000/sk/products`:

1. Middleware extracts: `domainId: 3`, `locale: 'sk'`
2. Rewrites to internal route: `/products` (Next.js page)
3. Domain context preserved throughout request

### URL Generation with Locale

```typescript
import { getBaseUrlWithLocale } from 'utils/domain/domainUtils';

// Generate URLs that respect locale paths
const baseUrl = 'http://127.0.0.1:8000';
getBaseUrlWithLocale(baseUrl, 'default'); // 'http://127.0.0.1:8000'
getBaseUrlWithLocale(baseUrl, 'sk'); // 'http://127.0.0.1:8000/sk'
```

## Troubleshooting

### Common Issues and Solutions

| Issue                                | Cause                            | Solution                                                                 |
| ------------------------------------ | -------------------------------- | ------------------------------------------------------------------------ |
| Users logged into wrong domain       | Cookies not properly isolated    | Verify cookie names include domain ID                                    |
| Lost locale after redirect           | Middleware not preserving locale | Check `getBaseUrlWithLocale` usage                                       |
| Domain not found errors              | Missing configuration            | Verify domain config in `next.config.js`                                 |
| Cookies visible across domains       | Using same cookie names          | Implement `getCookieName` utility                                        |
| Unexpected fallback redirect         | No exact domain match found      | Check domain configuration covers all expected URLs                      |
| Paths treated as locales             | Misunderstanding domain matching | Remember: only exact domain matches work, other paths are regular routes |
| Redirect when expecting direct serve | Domain URL doesn't match exactly | Ensure domain configuration includes trailing slashes consistently       |

## Summary

The Shopsys storefront domain configuration system provides:

- **Flexible multi-domain support** with optional locale-in-path URLs
- **Exact domain matching priority** with paths treated as regular application routes
- **Comprehensive domain coverage** in default configuration (base domains + locale-specific paths)
- **Fallback redirection** for edge cases with orphaned base URLs
- **Complete cookie isolation** between domains sharing the same host
- **Consistent API** for cookie operations across client and server
- **Automatic locale handling** in middleware and routing
- **Type-safe configuration** with TypeScript support

The name-based cookie isolation strategy ensures reliable authentication and state management across all domain configurations, eliminating race conditions and providing a seamless user experience.
