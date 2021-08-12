import { AppProps } from 'next/dist/next-server/lib/router/router';
import { appWithTranslation } from 'next-i18next';
import { GlobalErrorList } from 'components/blocks/errors/GlobalErrorList/GlobalErrorList';
import Popup from 'components/layout/Popup';
import { Provider } from 'react-redux';
import { ReactElement } from 'react';
import ShopsysGlobalErrorProvider from 'context/ShopsysGlobalErrorProvider';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';
import store from 'redux/store';

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

export default appWithTranslation(MyApp);
