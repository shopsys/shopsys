import { TooltipProvider } from 'components/Basic/Tooltip/Tooltip';
import { RouteAccessibilityManager } from 'components/Layout/RouteAccessibilityManager';
import { RouteAnnouncer } from 'components/Layout/RouteAnnouncer';
import { DeferredToastContainer } from 'components/Pages/App/DeferredToastContainer';
import { AuthorizationProvider } from 'components/providers/AuthorizationProvider';
import { CachedI18nProvider } from 'components/providers/CachedI18nProvider';
import { CookiesStoreProvider } from 'components/providers/CookiesStoreProvider';
import { CurrentCustomerUserProvider } from 'components/providers/CurrentCustomerUserProvider';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { PersistStoreProvider } from 'components/providers/PersistStoreProvider';
import { LazyMotion, MotionConfig } from 'framer-motion';
import { GtmProvider } from 'gtm/context/GtmProvider';
import { AppProps as NextAppProps } from 'next/app';
import dynamic from 'next/dynamic';
import 'nprogress/nprogress.css';
import { ReactElement, useEffect } from 'react';
import 'styles/globals.css';
import { isWithErrorDebugging } from 'utils/errors/isWithErrorDebugging';
import { logErrorBoundary } from 'utils/errors/logErrorBoundary';
import { logException } from 'utils/errors/logException';
import { initIntlDateTimeFormatterLocale } from 'utils/formaters/formatDate';

const framerMotionPlugins = () => import('utils/animations/framerMotionPlugins').then((res) => res.default);

type AppProps = {
    pageProps: any;
} & Omit<NextAppProps, 'pageProps'>;

process.on('unhandledRejection', (reason: unknown) =>
    logException({ reason, location: '_app.tsx:unhandledRejection' }),
);
process.on('uncaughtException', (error: Error, origin: unknown) =>
    logException({
        message: error.message,
        originalError: JSON.stringify(error),
        origin,
        location: '_app.tsx:uncaughtException',
    }),
);

const UrqlWrapper = dynamic(() => import('components/Layout/UrqlWrapper').then((component) => component.UrqlWrapper));

const AppPageContent = dynamic(() =>
    import('components/Pages/App/AppPageContent').then((component) => component.AppPageContent),
);

const ErrorBoundary = dynamic(() =>
    import('components/Basic/ErrorBoundary').then((component) => component.ErrorBoundary),
);

const Error500ContentWithBoundary = dynamic(
    () =>
        import('components/Pages/ErrorPage/Error500ContentWithBoundary').then(
            (component) => component.Error500ContentWithBoundary,
        ),
    { ssr: false },
);

const MinimalErrorContent = dynamic(() =>
    import('components/Pages/ErrorPage/MinimalErrorContent').then((component) => component.MinimalErrorContent),
);

function MyApp({ Component, pageProps }: AppProps): ReactElement | null {
    const domainConfig = pageProps.domainConfig;
    const defaultLocale = domainConfig?.defaultLocale ?? 'en';
    initIntlDateTimeFormatterLocale(defaultLocale);

    useEffect(() => {
        document.body.setAttribute('data-hydrated', 'true');
    }, []);

    // When domainConfig is missing (e.g., error page after getServerSideProps failed),
    // render minimal wrapper - just the page component without providers that need domainConfig.
    // This allows _error.tsx to render its ErrorPageBoundary and gracefully degrade.
    // The fallback uses MinimalErrorContent which has NO dependencies (no translations, no context).
    if (!domainConfig) {
        return (
            <TooltipProvider>
                <CachedI18nProvider pageProps={pageProps}>
                    <ErrorBoundary
                        fallbackRender={({ error }) => (
                            <MinimalErrorContent
                                err={error.message}
                                showDebugInfo={isWithErrorDebugging}
                                statusCode={500}
                            />
                        )}
                        onError={logErrorBoundary}
                    >
                        <Component {...pageProps} />
                    </ErrorBoundary>
                </CachedI18nProvider>
            </TooltipProvider>
        );
    }

    return (
        <TooltipProvider>
            <CachedI18nProvider pageProps={pageProps}>
                <ErrorBoundary
                    fallbackRender={({ error, resetErrorBoundary }) => (
                        <Error500ContentWithBoundary error={error} resetErrorBoundary={resetErrorBoundary} />
                    )}
                    onError={logErrorBoundary}
                >
                    <UrqlWrapper pageProps={pageProps}>
                        <CookiesStoreProvider cookieStoreStateFromServer={pageProps.cookiesStore}>
                            <DomainConfigProvider domainConfig={domainConfig}>
                                <PersistStoreProvider>
                                    <DeferredToastContainer />
                                    <CurrentCustomerUserProvider>
                                        <AuthorizationProvider customerUserRoles={pageProps.customerUserRoles}>
                                            <GtmProvider ipAddress={pageProps.ipAddress}>
                                                <MotionConfig reducedMotion="user">
                                                    <LazyMotion features={framerMotionPlugins}>
                                                        <RouteAccessibilityManager>
                                                            <RouteAnnouncer />
                                                            <AppPageContent
                                                                Component={Component}
                                                                pageProps={pageProps}
                                                            />
                                                        </RouteAccessibilityManager>
                                                    </LazyMotion>
                                                </MotionConfig>
                                            </GtmProvider>
                                        </AuthorizationProvider>
                                    </CurrentCustomerUserProvider>
                                </PersistStoreProvider>
                            </DomainConfigProvider>
                        </CookiesStoreProvider>
                    </UrqlWrapper>
                </ErrorBoundary>
            </CachedI18nProvider>
        </TooltipProvider>
    );
}

export default MyApp;
