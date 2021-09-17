import 'react-toastify/dist/ReactToastify.css';
import { AppProps } from 'next/app';
import { appWithTranslation } from 'next-i18next';
import { getDomainConfig } from '../utils/Domain/Domain';
import nextI18NextConfig from '../next-i18next.config.js';
import Popup from 'components/Layout/Popup';
import { Provider } from 'react-redux';
import { ReactElement } from 'react';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';
import store from 'redux/store';
import { ToastContainer } from 'react-toastify';
import { withUrqlClient } from 'next-urql';

function MyApp({ Component, pageProps }: AppProps): ReactElement {
    return (
        <Provider store={store}>
            <ShopsysGlobalProvider>
                <Popup />
                <ToastContainer autoClose={6000} position="top-center" theme="colored" />
                <Component {...pageProps} />
            </ShopsysGlobalProvider>
        </Provider>
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

export default withUrqlClient(
    () => ({
        url: getApiUrl(),
    }),
    { ssr: false },
)(
    // eslint-disable-next-line
    // @ts-ignore
    appWithTranslation(MyApp, nextI18NextConfig),
);
