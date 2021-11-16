import 'react-toastify/dist/ReactToastify.css';
import { AppProps } from 'next/app';
import { appWithTranslation } from 'next-i18next';
import { getDomainConfig } from 'utils/Domain/Domain';
import { getUrqlExchanges } from 'urql/exchanges';
import nextI18NextConfig from 'next-i18next.config';
import { nextReduxWrapper } from 'redux/main';
import { ReactElement } from 'react';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';
import { ToastContainer } from 'react-toastify';
import { useLoadCart } from 'hooks/cart/UseLoadCart';
import { withUrqlClient } from 'next-urql';

function MyApp({ Component, pageProps }: AppProps): ReactElement {
    useLoadCart();

    return (
        <ShopsysGlobalProvider>
            <ToastContainer autoClose={6000} position="top-center" theme="colored" />
            <Component {...pageProps} />
        </ShopsysGlobalProvider>
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
        appWithTranslation(MyApp, nextI18NextConfig),
    ),
);
