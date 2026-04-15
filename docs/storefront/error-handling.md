# Error handling on Storefront

This document describes how errors flow from the Backend API through the Storefront and how they are displayed to users.

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              BACKEND (PHP)                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│  Exception Classes              ErrorCodeSubscriber                         │
│  ┌─────────────────────┐       ┌──────────────────────────────────────────┐│
│  │ UserErrorWithCode   │──────>│ onErrorFormatting()                      ││
│  │ Interface           │       │ - Adds userCode to extensions            ││
│  │ - getUserErrorCode()│       │ - Adds code (404, 500) to extensions     ││
│  └─────────────────────┘       │ - Handles validation errors               ││
│                                └──────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
                            GraphQL Response
                    { errors: [{ message, extensions }] }
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           STOREFRONT (TypeScript)                           │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  errorExchange.ts (urql interceptor)                                        │
│  ┌─────────────────────────────────────────────────────────────────────────┐│
│  │ Intercepts ALL queries/mutations                                        ││
│  │ Routes to: handleErrorMessagesForDevelopment()                          ││
│  │            handleErrorMessagesForMutation()                             ││
│  │            handleErrorMessagesForUsers()                                ││
│  └─────────────────────────────────────────────────────────────────────────┘│
│                              │                                              │
│                              ▼                                              │
│  friendlyErrorMessageParser.ts                                              │
│  ┌─────────────────────────────────────────────────────────────────────────┐│
│  │ getUserFriendlyErrors() -> ParsedErrors                                 ││
│  │ - networkError: string                                                  ││
│  │ - applicationError: { type, message }                                   ││
│  │ - userError: { validation: Record<field, {message, code}> }             ││
│  └─────────────────────────────────────────────────────────────────────────┘│
│                              │                                              │
│        ┌─────────────────────┼─────────────────────┐                        │
│        ▼                     ▼                     ▼                        │
│  applicationErrors.ts   errorMessageMapper.ts   parseGraphqlError.ts        │
│  (verbosity levels)     (translations)          (extraction)                │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

## GraphQL Error Processing Flow

```
GraphQL Error
      │
      ▼
parseGraphqlError()
      │
      ▼
getUserFriendlyErrors()
      │
      ├── validationErrors? ──> userError.validation
      │                         (field-level errors set on form)
      │
      ├── userCode?
      │   ├── isNoLogError() ────────> applicationError (original message, silent)
      │   │
      │   ├── isNoFlashMessageError() ─> applicationError (original message, no toast)
      │   │
      │   └── isFlashMessageError() ──> applicationError (translated message, toast)
      │
      └── else ──────────────────────> default error ("Unknown error.")
                                       │
      ▼                                │
handleFormErrors() or errorExchange    │
      │                                │
      ├── Form: setError() for fields  │
      │         showErrorMessage() for unmatched fields
      │         (can override with errorMessage param)
      │
      └── Exchange: showErrorMessage() or silent
                    based on verbosity level
```

## GraphQL Error Types

The backend sends GraphQL errors with structured `extensions` containing error metadata:

**User Error Response** (with userCode):

```json
{
    "errors": [
        {
            "message": "Order with UUID 'xxx' not found.",
            "extensions": {
                "userCode": "order-not-found",
                "code": 404
            }
        }
    ]
}
```

**Validation Error Response**:

```json
{
    "errors": [
        {
            "message": "Validation failed",
            "extensions": {
                "userCode": null,
                "code": 500,
                "validation": {
                    "input.email": [
                        {
                            "message": "This value is not a valid email address.",
                            "code": "bd79c0ab-ddba-46cc-a703-a7a4b08de310"
                        }
                    ]
                }
            }
        }
    ]
}
```

## Application Error Verbosity Levels

Each error code in `applicationErrors.ts` is assigned a verbosity level that controls how it's handled:

| Level              | Behavior                                      | Example Error Codes                                          |
| ------------------ | --------------------------------------------- | ------------------------------------------------------------ |
| `flash-message`    | Show toast + log to Sentry                    | `cart-not-found`, `invalid-credentials`, `product-not-found` |
| `no-flash-message` | Log to Sentry, no toast (404 pages handle it) | `blog-category-not-found`, `article-not-found`               |
| `no-log`           | Silent - neither toast nor logging            | `invalid-token`, `no-result-found-for-slug`                  |

Only `flash-message` errors get translated via `errorMessageMapper.ts`. Other error types use the original API message.

## Mutation Error Handling in errorExchange

Cart-related mutations have centralized error handling in `errorExchange.ts` via `MUTATION_ERROR_CONFIG`. This prevents duplicate error messages and provides consistent error handling.

### Configuration

```typescript
const MUTATION_ERROR_CONFIG: Record<string, MutationErrorConfig> = {
    AddToCartMutation: {
        errorType: 'add-to-cart-error',
        gtmOrigin: GtmMessageOriginType.product_detail_page,
    },
    ApplyPromoCodeToCartMutation: {
        errorType: 'promo-code-apply-error',
        gtmOrigin: GtmMessageOriginType.cart,
        validationFields: ['promoCode'],
    },
    // ... more mutations
};
```

### How it works

1. `errorExchange` intercepts all mutation errors
2. Extracts mutation name from the GraphQL operation
3. Looks up config in `MUTATION_ERROR_CONFIG`
4. For validation errors on configured fields, shows the server-provided message
5. For other errors, shows generic error from `getErrorMessage(errorType, t)`

### Adding new mutations

To add centralized error handling for a new mutation:

1. Add entry to `MUTATION_ERROR_CONFIG` with:
    - `errorType`: Key from `FlashMessageKeys` for the generic error message
    - `gtmOrigin`: GTM tracking origin
    - `validationFields`: (optional) Array of field names for validation errors

2. Add the error type to `applicationErrors.ts` if needed
3. Add translation to `errorMessageMapper.ts` if using a new error type

### Network errors

Network errors for **queries** are silently ignored (no toast) to prevent toast spam when multiple queries fail simultaneously (e.g., on 404 pages). Only application errors trigger toasts from `errorExchange`.

## Form Error Handling

The `handleFormErrors()` function processes errors from form mutations:

```typescript
handleFormErrors(
    error, // CombinedError from mutation
    formProviderMethods, // react-hook-form methods
    t, // translate function
    errorMessage, // Optional: override API message
    fields, // Optional: field name mapping
    origin, // GTM tracking origin
);
```

**Key behavior**: The 4th parameter `errorMessage` can **override** the API's error message:

- Most forms pass `formMeta.messages.error` for a generic user-friendly message
- Login forms pass `undefined` to show actual API errors (e.g., "Invalid credentials")

| Pattern      | `errorMessage` Value                          | Use Case                             |
| ------------ | --------------------------------------------- | ------------------------------------ |
| Generic form | `formMeta.messages.error`                     | User-friendly generic message        |
| Login form   | `undefined`                                   | Show specific API error              |
| With fields  | `formMeta.messages.error` + `formMeta.fields` | Map validation errors to form fields |

## Error verbosity on Storefront

To ease the development process on Storefront, it is possible to change the error message verbosity. This is done by changing the environment variable `ERROR_DEBUGGING_LEVEL`, which can be one of these values:

| Mode                    | Console | Toast                | Features                             |
| ----------------------- | ------- | -------------------- | ------------------------------------ |
| `no-debug` (production) | No      | Simple user-friendly | User-friendly messages only          |
| `console`               | Yes     | No                   | Logs detailed errors to console      |
| `toast-and-console`     | Yes     | Verbose JSON         | JSON toasts with copy/ignore buttons |

Mind that this setting is independent of the node environment. This means that you can have full verbosity in a production-built application. Do not forget to limit the verbosity once you want to start showing your application to users.

### Error toasts

If your verbosity is set to `toast-and-console`, the error toast messages do not close automatically, they are also not closable by just clicking anywhere on them. This is because they contain a copy-text box with the full error message. You can thus easily copy the full error message in a JSON format.

You can also **ignore specific error types** during development - they are stored in localStorage and persist across sessions.

### Exceptions

If your verbosity is set to `toast-and-console`, the error page for 500 errors also shows a copy-text box with the underlying error. Because of inner Next.js workings, it is impossible to provide a simple 'copy text' button, but you can still easily copy the entire error message in a JSON format.

## The `logException` function

This function will be your friend while logging exceptions anywhere in the app. It checks the current environment and based on it logs it to the console (development) and sends the error to Sentry. You should use it to make sure the errors are correctly displayed both in the console and in Sentry.

## Run-time error on the server (`getServerSideProps` or `getInitialProps`)

- **In production** - The error is propagated to the `_error.tsx` page with a status code **500**. We do not need to handle the error inside `getServerSideProps` or `getInitialProps`as it is handled inside the error page, where it is available inside `context.err`. The user is only shown `<Error500Content />`, the status code is **500**. A default error message (`500 - Internal Server Error`) is logged into the console, meaning the underlying error is unknown to the user.

- **In development** - The error is thrown and shown to the developer right away using the Next.js error popup. You can also see it in the container logs to make sure it is a SSR error.

## Run-time error on the client (inside error boundary)

- **Production**: Caught by `ErrorBoundary` in `_app.tsx`, displays `Error500ContentWithBoundary`.
- **Development**: Next.js error popup shows the error.

### 503 Error (Maintenance)

Handled specially via `isMaintenance` flag in server-side props:

```tsx
// In getServerSideProps
const isMaintenance = resolvedQueries.some((query) => query.error?.response?.status === 503);
if (isMaintenance) {
    context.res.statusCode = 503;
}

// In AppPageContent.tsx
{
    pageProps.isMaintenance ? <Error503Content /> : <Component {...pageProps} />;
}
```

### 404 Error (Not found)

This error is (in the end) always handled inside `_error.tsx`. However, the error can get there in multiple different ways. One is if a friendly URL page is not found and the following is called in the middleware:

```tsx
return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
    headers: [
        [MIDDLEWARE_STATUS_CODE_KEY, '404'],
        [MIDDLEWARE_STATUS_MESSAGE_KEY, 'Friendly URL page not found'],
    ],
});
```

As you can see, we do not throw an error, but rather return a **rewrite**. Through this, the `_error.tsx` is hit and the error is handled there.

Another way we can get to the `_error.tsx` page, is by returning a `notFound` pointer from `getServerSideProps`, which you can do either by directly returning`{ notFound: true }`, or if your page is a friendly URL page, by handling your errors using `handleServerSideErrorResponseForFriendlyUrls`:

```tsx
export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            ...
            if (isRedirectedFromSsr(context.req.headers)) {
                ...
                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    categoryDetailResponse.error?.graphQLErrors,
                    categoryDetailResponse.data?.category,
                    context.res,
                );

                if (serverSideErrorResponse) {
                    return serverSideErrorResponse;
                }
            }
            ...
        },
);
```

The last step is to handle it in the `_error.tsx` page. You can see that we do not log the exception for 404, as this would flood Sentry. However, we do log it if the `errorDebuggingLevel` is set to `console` or `toast-and-console` (env variable `ERROR_DEBUGGING_LEVEL`is set). Keep this in mind, as having this setting in an environment which includes Sentry might cause a lot of logs.

```tsx
ErrorPage.getInitialProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context: any) => {
    const middlewareStatusCode = Number.parseInt(context.res.getHeader(MIDDLEWARE_STATUS_CODE_KEY) || '');
    ...
    const statusCode = middlewareStatusCode || context.res.statusCode || 500;
    ...

    if (statusCode !== 404 || isWithErrorDebugging) {
        logException({
            message: err,
            statusCode,
            initServerSidePropsResonse: JSON.stringify(serverSideProps),
            location: 'ErrorPage.getInitialProps.noErrorDebugging',
        });
    }

    if (isWithToastAndConsoleErrorDebugging) {
        showErrorMessage(err);
    }

    // Intentionally assign the response status after logging and toast handling.
    context.res.statusCode = statusCode;
    ...

    return {
        ...props,
        statusCode,
        ...
    } as ErrorPageProps;
});

const ErrorPage: NextPage<ErrorPageProps> = ({ hasGetInitialPropsRun, err, statusCode }): ReactElement => {
    ...
    return statusCode === 404 ? <Error404Content /> : <Error500Content />;
};
```

## Handling friendly URL page errors

If a 'core' GraphQL request for a friendly URL page (e.g. product detail query for the product detail page) fails with a 500 code, an error is directly thrown on Storefront. Both on the client and on the server. On the server, this is handled using `handleServerSideErrorResponseForFriendlyUrls`, which can be used because we have a direct access to the failed query. On the client, this is done globally in the `errorExchange`. Here, each operation is checked for a special directive `@friendlyUrl`. If an operation with such directive fails with a 500 code on the client, an error is thrown. This means that for this mechanism to work, you have to add this directive to your queries, which are considered 'core' queries for your friendly URL pages.

## Form Error Handling Guidelines

### When to use `useErrorHandler()`

Use the `useErrorHandler()` hook for all form mutations and API calls. It provides centralized error handling that:

1. Parses errors using `getUserFriendlyErrors()` internally
2. Sets validation errors on form fields via `setError()` automatically
3. Shows toast messages for non-field errors
4. Logs errors to Sentry when appropriate
5. Supports custom handlers for specific error types

### Standard pattern

```typescript
import { useErrorHandler } from 'utils/errors/useErrorHandler';

const MyFormComponent = () => {
    const [formProviderMethods] = useMyForm();
    const formMeta = useMyFormMeta(formProviderMethods);

    const handleError = useErrorHandler({
        form: formProviderMethods,
        gtmOrigin: GtmMessageOriginType.other,
        customMessage: formMeta.messages.error,
    });

    const onSubmit = async (data: FormData) => {
        const result = await mutation({ input: data });
        handleError(result.error);

        if (result.data) {
            // Success handling
        }
    };
};
```

### Login form pattern (showing actual API errors)

To show the actual API error message (e.g., "Invalid credentials") instead of a generic message, omit the `customMessage` option:

```typescript
const handleError = useErrorHandler({
    form: formProviderMethods,
    gtmOrigin: GtmMessageOriginType.login_popup,
    // No customMessage - shows actual API error like "Invalid credentials"
});

const onSubmit = async (data: LoginFormData) => {
    const result = await login({ input: data });
    handleError(result.error);

    if (result.data) {
        // Login success
    }
};
```

### Custom error type handling

For specific error types that require special handling, use the `customHandlers` option:

```typescript
const handleError = useErrorHandler({
    form: formProviderMethods,
    gtmOrigin: GtmMessageOriginType.other,
    customMessage: formMeta.messages.error,
    customHandlers: {
        'invalid-account-or-password': () => {
            formProviderMethods.setError('oldPassword', {
                message: t('The current password is incorrect'),
            });
        },
    },
});

const onSubmit = async (data: ChangePasswordFormData) => {
    const result = await changePassword({ input: data });
    handleError(result.error);

    if (result.data) {
        // Success handling
    }
};
```

### Manual field errors before delegating

If you need to set specific field errors before delegating to the centralized handler:

```typescript
const handleError = useErrorHandler({
    form: formProviderMethods,
    gtmOrigin: GtmMessageOriginType.other,
});

const onSubmit = async (data: FormData) => {
    const result = await mutation({ input: data });

    // Handle specific case manually first
    if (result.error) {
        const { applicationError } = getUserFriendlyErrors(result.error, t);
        if (applicationError?.type === 'special-field-error') {
            formProviderMethods.setError('specificField', {
                message: t('Custom error message'),
            });
            return;
        }
    }

    // Delegate remaining errors to centralized handler
    handleError(result.error);

    if (result.data) {
        // Success handling
    }
};
```

### Hook options reference

| Option           | Type                                                 | Description                              |
| ---------------- | ---------------------------------------------------- | ---------------------------------------- |
| `form`           | `UseFormReturn<TFormValues>`                         | Form instance for setting field errors   |
| `gtmOrigin`      | `GtmMessageOriginType`                               | GTM tracking origin (default: `other`)   |
| `customMessage`  | `string`                                             | Override error message for toasts        |
| `customHandlers` | `Partial<Record<ApplicationErrorsType, () => void>>` | Custom handlers for specific error types |

## Error Code Reference

### Flash-Message Errors

Show a toast message to the user and log to Sentry. These are user-facing errors that require attention. Translations are defined in `errorMessageMapper.ts`.

### No-Flash-Message Errors

Log to Sentry but don't show toasts. Typically used for 404-type errors where the page itself handles the error display (e.g., showing a "not found" page).

### No-Log Errors

Silent errors - neither toast nor logging. Used for expected behavior that doesn't need user notification or tracking (e.g., "product already in wishlist" when user clicks add again).

---

For the complete and up-to-date list of error codes, see:

- **`utils/errors/applicationErrors.ts`** - Defines all error codes and their verbosity levels
- **`utils/errors/errorMessageMapper.ts`** - Maps error codes to translated messages

## Testing Errors in Styleguide

Navigate to `/styleguide` and scroll to "Error Handling" section:

1. **Application Error Buttons**: Trigger different error types
2. **Validation Error Simulator**: Test field-level errors
3. **Debug Mode Status**: Shows current verbosity level
4. **Ignored Errors Manager** (debug mode): Manage suppressed error types

## Adding New Error Codes

1. Add the code to `applicationErrors.ts` with appropriate verbosity level
2. If `flash-message`, add translation to `errorMessageMapper.ts`
3. Document the code in this file
