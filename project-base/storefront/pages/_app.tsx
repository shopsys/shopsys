import { CookiesStoreProvider } from 'components/providers/CookiesStoreProvider';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { logException } from 'helpers/errors/logException';
import { initDayjsLocale } from 'helpers/formaters/formatDate';
import i18nConfig from 'i18n';
import appWithI18n from 'next-translate/appWithI18n';
import { AppProps as NextAppProps } from 'next/app';
import dynamic from 'next/dynamic';
import 'nprogress/nprogress.css';
import { ReactElement } from 'react';
import 'react-loading-skeleton/dist/skeleton.css';
import 'react-toastify/dist/ReactToastify.css';
import 'styles/globals.css';
import 'styles/user-text.css';

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

const UrqlWrapper = dynamic(() => import('components/Layout/UrqlWrapper').then((component) => component.UrqlWrapper), {
    ssr: true,
});

const AppPageContent = dynamic(
    () => import('components/Pages/App/AppPageContent').then((component) => component.AppPageContent),
    {
        ssr: true,
    },
);

const ErrorBoundary = dynamic(() => import('react-error-boundary').then((component) => component.ErrorBoundary), {
    ssr: true,
});

const Error500ContentWithBoundary = dynamic(
    () =>
        import('components/Pages/ErrorPage/Error500Content').then((component) => component.Error500ContentWithBoundary),
    {
        ssr: true,
    },
);

function MyApp({ Component, pageProps }: AppProps): ReactElement | null {
    const { defaultLocale } = pageProps.domainConfig;
    initDayjsLocale(defaultLocale);

    return (
        <ErrorBoundary FallbackComponent={Error500ContentWithBoundary}>
            <UrqlWrapper pageProps={pageProps}>
                <CookiesStoreProvider>
                    <DomainConfigProvider domainConfig={pageProps.domainConfig}>
                        <AppPageContent Component={Component} pageProps={pageProps} />
                    </DomainConfigProvider>
                </CookiesStoreProvider>
            </UrqlWrapper>
        </ErrorBoundary>
    );
}

// eslint-disable-next-line
// @ts-ignore
export default appWithI18n(MyApp, { ...i18nConfig });
