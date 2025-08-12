import { AuthorizationProvider } from 'components/providers/AuthorizationProvider';
import { CookiesStoreProvider } from 'components/providers/CookiesStoreProvider';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { LazyMotion, MotionConfig } from 'framer-motion';
import { GtmProvider } from 'gtm/context/GtmProvider';
import i18nConfig from 'i18n';
import appWithI18n from 'next-translate/appWithI18n';
import { AppProps as NextAppProps } from 'next/app';
import dynamic from 'next/dynamic';
import 'nprogress/nprogress.css';
import { ReactElement, useEffect } from 'react';
import 'react-toastify/dist/ReactToastify.css';
import 'styles/globals.css';
import { logErrorBoundary } from 'utils/errors/logErrorBoundary';
import { logException } from 'utils/errors/logException';
import { initDayjsLocale } from 'utils/formaters/formatDate';

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

const ErrorBoundary = dynamic(() => import('react-error-boundary').then((component) => component.ErrorBoundary));

const Error500ContentWithBoundary = dynamic(
    () =>
        import('components/Pages/ErrorPage/Error500ContentWithBoundary').then(
            (component) => component.Error500ContentWithBoundary,
        ),
    { ssr: false },
);

function MyApp({ Component, pageProps }: AppProps): ReactElement | null {
    const { defaultLocale } = pageProps.domainConfig;
    initDayjsLocale(defaultLocale);

    useEffect(() => {
        document.body.setAttribute('data-hydrated', 'true');
    }, []);

    return (
        <ErrorBoundary
            fallbackRender={({ error, resetErrorBoundary }) =>
                error ? <Error500ContentWithBoundary error={error} resetErrorBoundary={resetErrorBoundary} /> : null
            }
            onError={logErrorBoundary}
        >
            <UrqlWrapper pageProps={pageProps}>
                <CookiesStoreProvider cookieStoreStateFromServer={pageProps.cookiesStore}>
                    <DomainConfigProvider domainConfig={pageProps.domainConfig}>
                        <AuthorizationProvider customerUserRoles={pageProps.customerUserRoles}>
                            <GtmProvider>
                                <MotionConfig reducedMotion="user">
                                    <LazyMotion features={framerMotionPlugins}>
                                        <AppPageContent Component={Component} pageProps={pageProps} />
                                    </LazyMotion>
                                </MotionConfig>
                            </GtmProvider>
                        </AuthorizationProvider>
                    </DomainConfigProvider>
                </CookiesStoreProvider>
            </UrqlWrapper>
        </ErrorBoundary>
    );
}

// eslint-disable-next-line
// @ts-ignore
export default appWithI18n(MyApp, { ...i18nConfig });
