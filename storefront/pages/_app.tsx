import { AppProps } from 'next/dist/next-server/lib/router/router';
import { appWithTranslation } from 'next-i18next';
import { GlobalErrorList } from 'components/blocks/errors/GlobalErrorList/GlobalErrorList';
import { ReactElement } from 'react';
import ShopsysGlobalErrorProvider from 'context/ShopsysGlobalErrorProvider';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';

function MyApp({ Component, pageProps }: AppProps): ReactElement {
    return (
        <ShopsysGlobalProvider>
            <ShopsysGlobalErrorProvider>
                <GlobalErrorList />
                <Component {...pageProps} />
            </ShopsysGlobalErrorProvider>
        </ShopsysGlobalProvider>
    );
}

export default appWithTranslation(MyApp);
