import { AppProps } from 'next/dist/next-server/lib/router/router';
import { appWithTranslation } from 'next-i18next';
import { ReactElement } from 'react';
import ShopsysGlobalProvider from 'components/ShopsysGlobalProvider';
import { theme } from 'theme/main';

function MyApp({ Component, pageProps }: AppProps): ReactElement {
    return (
        <ShopsysGlobalProvider theme={theme}>
            <Component {...pageProps} />
        </ShopsysGlobalProvider>
    );
}

export default appWithTranslation(MyApp);
