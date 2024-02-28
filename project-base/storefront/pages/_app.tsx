import { AppPageContent } from 'components/Pages/App/AppPageContent';
import { Error500ContentWithBoundary } from 'components/Pages/ErrorPage/Error500Content';
import { CookiesStoreProvider } from 'components/providers/cookiesStoreProvider';
import { logException } from 'helpers/errors/logException';
import { initDayjsLocale } from 'helpers/formaters/formatDate';
import { ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import i18nConfig from 'i18n';
import appWithI18n from 'next-translate/appWithI18n';
import useTranslation from 'next-translate/useTranslation';
import { AppProps as NextAppProps } from 'next/app';
import 'nprogress/nprogress.css';
import { ReactElement, useMemo } from 'react';
import { ErrorBoundary } from 'react-error-boundary';
import 'react-loading-skeleton/dist/skeleton.css';
import 'react-toastify/dist/ReactToastify.css';
import 'styles/globals.css';
import 'styles/user-text.css';
import { Provider, ssrExchange } from 'urql';
import { createClient } from 'urql/createClient';

type AppProps = {
    pageProps: ServerSidePropsType;
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

function MyApp({ Component, pageProps }: AppProps): ReactElement | null {
    const { defaultLocale, publicGraphqlEndpoint } = pageProps.domainConfig;
    initDayjsLocale(defaultLocale);
    const { t } = useTranslation();

    const urqlClient = useMemo(
        () =>
            createClient({ t, ssrExchange: ssrExchange({ initialState: pageProps.urqlState }), publicGraphqlEndpoint }),
        [publicGraphqlEndpoint, pageProps.urqlState, t],
    );

    return (
        <ErrorBoundary FallbackComponent={Error500ContentWithBoundary}>
            <Provider value={urqlClient}>
                <CookiesStoreProvider>
                    <AppPageContent Component={Component} pageProps={pageProps} />
                </CookiesStoreProvider>
            </Provider>
        </ErrorBoundary>
    );
}

// eslint-disable-next-line
// @ts-ignore
export default appWithI18n(MyApp, { ...i18nConfig });
