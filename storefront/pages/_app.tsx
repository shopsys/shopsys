import { AppProps } from 'next/dist/next-server/lib/router/router';
import { appWithTranslation } from 'next-i18next';
import { GlobalErrorList } from '../components/blocks/errors/GlobalErrorList/GlobalErrorList';
import { ReactElement } from 'react';
import ShopsysGlobalProvider from 'components/ShopsysGlobalProvider';
import { SsfwGlobalErrorProvider } from '../components/SsfwGlobalErrorProvider/SsfwGlobalErrorProvider';
import { theme } from 'theme/main';

function MyApp({ Component, pageProps }: AppProps): ReactElement {
    return (
        <ShopsysGlobalProvider theme={theme}>
            <SsfwGlobalErrorProvider>
                <GlobalErrorList />
                <Component {...pageProps} />
            </SsfwGlobalErrorProvider>
        </ShopsysGlobalProvider>
    );
}

export default appWithTranslation(MyApp);
