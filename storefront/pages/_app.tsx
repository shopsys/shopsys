import { PortalContainer } from 'components/Basic/Portal/Portal.style';
import Error500 from 'components/Pages/ErrorPage/500';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';
import i18nConfig from 'i18n';
import appWithI18n from 'next-translate/appWithI18n';
import { withUrqlClient } from 'next-urql';
import { AppProps } from 'next/app';
import Head from 'next/head';
import { ReactElement } from 'react';
import { ErrorBoundary } from 'react-error-boundary';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { nextReduxWrapper } from 'redux/main';
import { getUrqlExchanges } from 'urql/exchanges';
import { getDomainConfig } from 'utils/Domain/Domain';

type AppPropsWithError = AppProps & {
    err?: any;
};

function MyApp({ Component, pageProps, err }: AppPropsWithError): ReactElement {
    return (
        <>
            <Head>
                <link rel="preload" href="/fonts/dmSans400ext.woff2" as="font" type="font/woff2" crossOrigin="" />
                <link rel="preload" href="/fonts/dmSans400.woff2" as="font" type="font/woff2" crossOrigin="" />
                <link rel="preload" href="/fonts/dmSans500ext.woff2" as="font" type="font/woff2" crossOrigin="" />
                <link rel="preload" href="/fonts/dmSans500.woff2" as="font" type="font/woff2" crossOrigin="" />
                <link rel="preload" href="/fonts/dmSans700ext.woff2" as="font" type="font/woff2" crossOrigin="" />
                <link rel="preload" href="/fonts/dmSans700.woff2" as="font" type="font/woff2" crossOrigin="" />
            </Head>
            <ShopsysGlobalProvider>
                <PortalContainer id="portal" />
                <ToastContainer autoClose={6000} position="top-center" theme="colored" />
                <ErrorBoundary FallbackComponent={Error500}>
                    <Component {...pageProps} err={err} />
                </ErrorBoundary>
            </ShopsysGlobalProvider>
        </>
    );
}

/**
 * We need to define "something" on the server side, even though it is not used at all.
 * On the server side, the URL is actually defined in initUrqlClient in InitServerSideProps.
 */
const getApiUrl = () => {
    let apiUrl = 'defaultUrl';
    if (typeof window !== 'undefined') {
        apiUrl = getDomainConfig(window.location.host).publicGraphqlEndpoint;
    }
    return apiUrl;
};

export default nextReduxWrapper.withRedux(
    withUrqlClient(
        (ssrExchange) => ({
            url: getApiUrl(),
            exchanges: getUrqlExchanges(ssrExchange),
        }),
        { ssr: false },
    )(
        // eslint-disable-next-line
        // @ts-ignore
        appWithI18n(MyApp, { ...i18nConfig }),
    ),
);
