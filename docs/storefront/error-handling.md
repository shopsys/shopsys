# Error handling on Storefront

## The `logException` function

This function will be your friend while logging exceptions anywhere in the app. It checks the current environemnt and based on it logs it to the console (development) and sends the error to Sentry. You should use it to make sure the errors are correctly displayed both in the console and in Sentry.

## Run-time error on the server (`getServerSideProps` or `getInitialProps`)

- **In production** - The error is propagated to the `_error.tsx` page with a status code **500**. We do not need to handle the error inside `getServerSideProps` or `getInitialProps`as it is handled inside the error page, where it is available inside `context.err`. The user is only shown `<Error500Content />`, the status code is **500**. A default error message (`500 - Internal Server Error`) is logged into the console, meaning the underlying error is unknown to the user.

- **In development** - The error is thrown and shown to the developer right away using the Next.js error popup. You can also see it in the container logs to make sure it is a SSR error.

## Run-time error on the client (iniside error boundary)

- **In production** - Here you need to make sure what "on the client" means. This means that the error was not present during SSR and only happened on the client. If that was the case, the error is caught by the nearest error boundary (in our case there is only one in `_app.tsx`). This error boundary should have a fallback component that then tries to handle the error and reset it. In our case, the global error boundary displays `Error500ContentWithBoundary`which can be (at least visually) easily confused with `Error500Content`, but the difference is that it also handles resetting of the error.

- **In development** - The error is thrown and shown to the developer right away using the Next.js error popup.

## Run-time error on the client (outside error boundary)

- **In production** - The error is not handled, therefore the below code in  `_app.tsx` is triggered. The rendered result does not show our custom `<Error500Content />`, only a white page with a simple error message.

```typescript
process.on('unhandledRejection', logException);
process.on('uncaughtException', logException);
```

- **In development** - The error is thrown and shown to the developer right away using the Next.js error popup.

## Run-time error in middleware

- **In production** and  **in development** - Since the entire middleware is wrapped in a `try-catch` block, the error is propagated to the `_error.tsx` page, where the value under `MIDDLEWARE_STATUS_CODE_KEY` is used as the status code and the value under `MIDDLEWARE_STATUS_MESSAGE_KEY` as the message error

```typescript
    } catch (e) {
        return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
            headers: [
                [MIDDLEWARE_STATUS_CODE_KEY, '500'],
                [MIDDLEWARE_STATUS_MESSAGE_KEY, `Middleware runtime error (${JSON.stringify(e)}})`],
            ],
        });
    }
```

## 503 Error (Maintenance)

This is not an error per se, but we need to handle this as such. In this case, we handle it specifically, as Next does not allow us to handle it through the `_error.tsx` page. This means you can only achieve sending the **503** status and showing the maintenance component in the following way. Because of it, both on the **in production** and  **in development**, we handle it by rewriting the response status code in **initServerSideProps**:

```tsx
const isMaintenance = resolvedQueries.some((query) => query.error?.response?.status === 503);
if (isMaintenance) {
    // eslint-disable-next-line require-atomic-updates
    context.res.statusCode = 503;
}
```

and then by passing a special boolean pointer in the server-side props:

```tsx
return {
    props: {
        ...
        isMaintenance // This is the previously defined variable,
    },
};
```

This pointer is then used in `AppPageContent.tsx`, where we display a special (page content) component:

```tsx
export const AppPageContent: FC<AppPageContentProps> = ({ Component, pageProps }) => {
    ...

    return (
        <>
            ...
            <ErrorBoundary FallbackComponent={Error500ContentWithBoundary}>
                ...
                {pageProps.isMaintenance ? <Error503Content /> : <Component {...pageProps} />}
            </ErrorBoundary>
        </>
    );
```

## 404 Error (Not found)

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

Another way we can get to the `_error.tsx` page, is by returning a `notFound` pointer from `getServerSideProps`, such as we do on the category detail page:

```tsx
export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            ...
            if (isRedirectedFromSsr(context.req.headers)) {
                ...
                if (!categoryDetailResponse.data?.category && !(context.res.statusCode === 503)) {
                    return {
                        notFound: true,
                    };
                }
            }
            ...
        },
);
```

The last step is to handle it in the `_error.tsx` page. You can see that we do not log the exception for 404, as this would flood Sentry.

```tsx
ErrorPage.getInitialProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context: any) => {
    const middlewareStatusCode = Number.parseInt(context.res.getHeader(MIDDLEWARE_STATUS_CODE_KEY) || '');
    ...
    const statusCode = middlewareStatusCode || context.res.statusCode || 500;
    ...

    if (statusCode !== 404) {
        logException(err);
    }

    // eslint-disable-next-line require-atomic-updates
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
