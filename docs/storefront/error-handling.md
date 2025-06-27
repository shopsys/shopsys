# Error handling on Storefront

## Error verbosity on Storefront

To ease the development process on Storefront, it is possible to change the error message verbosity. This is done by changing the environment variable `ERROR_LEVEL_DEBUGGING`, which can be one of these values:

- `console` - all messages are shown with their full verbosity, this includes GraphQL errors and runtime exceptions, but they are only logged in the console
- `toast-and-console` - all messages are shown with their full verbosity, this includes GraphQL errors and runtime exceptions, they are shown both in the console and logged as a toast message
- `no-debug` - messages are shown as the user would see them, so no debug in console or toasts

Mind that this setting is independent of the node environment. This means that you can have full verbosity in a production-built application. Do not forget to limit the verbosity once you want to start showing your application to users.

### Error toasts

If your verbosity is set to `toast-and-console`, the error toast messages do not close automatically, they are also not closable by just clicking anywhere on them. This is because they contain a copy-text box with the full error message. You can thus easily copy the full error message in a JSON format.

## App Router error boundaries

With Next.js App Router, error handling is managed through a hierarchy of error boundaries using special files:

- `global-error.tsx` - Catches errors in the root layout and other global errors
- `error.tsx` - Catches errors in page components and their children
- `not-found.tsx` - Handles 404 errors and not found cases

### Error boundary hierarchy

```
Error Occurs → Layout/Provider errors → global-error.tsx
            → 404 errors → not-found.tsx
            → Component errors → error.tsx
```

## The `logException` function

This function will be your friend while logging exceptions anywhere in the app. It checks the current environment and based on it logs it to the console (development) and sends the error to Sentry. You should use it to make sure the errors are correctly displayed both in the console and in Sentry.

## Global errors (`global-error.tsx`)

Global errors are the most critical errors that occur at the application level. These include:

- Errors in the root layout
- Provider initialization errors
- Critical runtime errors that bubble up from the entire application

**In production** - The user sees a clean error page with options to retry or go home. The error is automatically reported to Sentry.

**In development** - Full error details are shown in an expandable section, including stack traces and error messages.

```tsx
// Example of global error handling
useEffect(() => {
    // Report all global errors to Sentry as they are critical
    Sentry.captureException(error);
}, [error]);
```

## Page-level errors (`error.tsx`)

Page-level errors catch errors that occur within page components and their children. These errors are handled more gracefully than global errors.

**404 error filtering** - The error boundary automatically detects 404 errors and delegates them to `not-found.tsx` for proper handling.

**In production** - Users see a styled error page with options to try again or go back. Non-critical errors may not be reported to Sentry to avoid noise.

**In development** - Error details are shown with the error message and digest for debugging.

```tsx
// 404 errors are filtered out and handled by not-found.tsx
const isNotFound = error.message?.includes('NEXT_NOT_FOUND') || error.digest?.includes('NEXT_NOT_FOUND');

if (isNotFound) {
    throw error; // Re-throw to let Next.js handle with not-found.tsx
}
```

## Server-side errors

Server-side errors occur during:

- Server components rendering
- Data fetching in server components
- API route handlers

**In production** - Server errors are caught by the appropriate error boundary (`error.tsx` or `global-error.tsx`) and displayed to the user with a clean interface.

**In development** - Next.js shows detailed error overlays with stack traces and helpful debugging information.

## Client-side errors

Client-side errors occur in:

- Client components after hydration
- Event handlers
- Async operations in useEffect

These errors are caught by the nearest error boundary in the component tree.

## Error testing

A dedicated test page is available at `/test-errors` (development only) that allows testing different error scenarios:

- **Render Error** - Throws an error during component rendering
- **Async Error** - Throws an error in an async operation
- **Network Error** - Simulates a network request failure
- **Global Error** - Tests global error boundary (works best in production)

### Development vs Production Error Handling

**Important:** Error boundaries behave differently in development vs production:

- **Development:** Next.js shows its own error overlay for most errors, including those that would normally be caught by error boundaries
- **Production:** Error boundaries work as expected, showing your custom error pages

To properly test error boundaries:

1. Build the application: `npm run build`
2. Start in production mode: `npm start`
3. Navigate to `/test-errors` and test different scenarios

### Testing Global Errors

Global errors (`global-error.tsx`) are only triggered by:

- Errors in the root layout (`layout.tsx`)
- Errors in provider components that wrap the entire app
- Errors that occur during the initial app shell rendering
- JavaScript errors that occur outside of React's component tree (these won't be caught by any error boundary)

**Important:** Most component errors will be caught by the page-level `error.tsx`, not `global-error.tsx`. The global error boundary is a last resort for critical application-level failures.

**Real-world scenarios for global errors:**
- Provider initialization failures (auth, theme, etc.)
- Root layout rendering errors
- Critical JavaScript runtime errors

**Testing limitation:** In a typical page component, errors will be caught by `error.tsx` rather than bubbling up to `global-error.tsx`. To test global errors properly, you would need to:
1. Throw an error in the root layout
2. Cause a provider-level error
3. Trigger an error during app initialization

In development, these errors will show the Next.js error overlay instead of your custom global error page.

## Error reporting with Sentry

All critical errors are automatically reported to Sentry with:

- Error message and stack trace
- User context and session information
- Environment and build information
- Custom tags for error categorization

```tsx
// Automatic error reporting
Sentry.captureException(error, {
    tags: {
        errorBoundary: 'global',
        component: 'GlobalErrorPage',
    },
});
```

## Best practices

### Error boundary placement

- Use `global-error.tsx` for critical application-level errors
- Use `error.tsx` for page-level errors that can be recovered
- Use `not-found.tsx` for 404 and missing resource errors

### Error reporting

- Report all global errors to Sentry
- Be selective with page-level error reporting to avoid noise
- Include relevant context and tags for better error tracking

### User experience

- Provide clear error messages without technical details in production
- Offer recovery options (retry, go back, go home)
- Show detailed error information only in development

### Development debugging

- Use the `/test-errors` page to test error scenarios
- Check both client and server error handling
- Verify error reporting to Sentry in staging environments

## Migration from Pages Router

The App Router error handling system replaces the previous Pages Router approach:

- `_error.tsx` → `error.tsx` and `global-error.tsx`
- Error boundaries are now built into Next.js
- No need for custom error boundary components
- Automatic error recovery and reset functionality
