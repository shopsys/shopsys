import { AppProps } from 'next/app';
import { appWithTranslation } from 'next-i18next';
import getConfig from 'next/config';
import { GlobalErrorList } from 'components/blocks/errors/GlobalErrorList/GlobalErrorList';
import nextI18NextConfig from '../next-i18next.config.js';
import Popup from 'components/layout/Popup';
import { Provider } from 'react-redux';
import { ReactElement } from 'react';
import ShopsysGlobalErrorProvider from 'context/ShopsysGlobalErrorProvider';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';
import store from 'redux/store';
import { withUrqlClient } from 'next-urql';

const { publicRuntimeConfig } = getConfig();

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

export default withUrqlClient(
    () => ({
        url: publicRuntimeConfig.publicGraphqlEndpoint,
    }),
    { ssr: false },
)(
    // eslint-disable-next-line
    // @ts-ignore
    appWithTranslation(MyApp, nextI18NextConfig),
);
