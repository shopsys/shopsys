import 'react-toastify/dist/ReactToastify.css';
import { AppProps } from 'next/app';
import appWithI18n from 'next-translate/appWithI18n';
import { getDomainConfig } from 'utils/Domain/Domain';
import { getUrqlExchanges } from 'urql/exchanges';
import Head from 'next/head';
import i18nConfig from 'i18n';
import { nextReduxWrapper } from 'redux/main';
import { PortalContainer } from 'components/Basic/Portal/Portal.style';
import { ReactElement } from 'react';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';
import { ToastContainer } from 'react-toastify';
import { withUrqlClient } from 'next-urql';

function MyApp({ Component, pageProps }: AppProps): ReactElement {
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
                <Component {...pageProps} />
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
