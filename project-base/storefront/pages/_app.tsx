import { AppPageContent } from 'components/Pages/App/AppPageContent';
import { Error500ContentWithBoundary } from 'components/Pages/ErrorPage/Error500Content';
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

process.on('unhandledRejection', logException);
process.on('uncaughtException', logException);

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
                <AppPageContent Component={Component} pageProps={pageProps} />
            </Provider>
        </ErrorBoundary>
    );
}

// eslint-disable-next-line
// @ts-ignore
export default appWithI18n(MyApp, { ...i18nConfig });
