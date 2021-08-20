import { AppProps } from 'next/app';
import { appWithTranslation } from 'next-i18next';
import { getDomainConfig } from '../utils/Domain/Domain';
import { GlobalErrorList } from 'components/blocks/errors/GlobalErrorList/GlobalErrorList';
import nextI18NextConfig from '../next-i18next.config.js';
import Popup from 'components/layout/Popup';
import { Provider } from 'react-redux';
import { ReactElement } from 'react';
import ShopsysGlobalErrorProvider from 'context/ShopsysGlobalErrorProvider';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';
import store from 'redux/store';
import { withUrqlClient } from 'next-urql';

function MyApp({ Component, pageProps }: AppProps): ReactElement {
    return (
        <Provider store={store}>
            <ShopsysGlobalProvider>
                <ShopsysGlobalErrorProvider>
                    <Popup />
                    <GlobalErrorList />
                    <Component {...pageProps} />
                </ShopsysGlobalErrorProvider>
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
