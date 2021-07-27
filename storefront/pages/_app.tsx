import { ReactElement, useEffect, useState } from 'react';
import { AppProps } from 'next/dist/next-server/lib/router/router';
import i18n from 'config/i18n';
import { I18nextProvider } from 'react-i18next';
import ShopsysGlobalProvider from 'components/ShopsysGlobalProvider';
import { theme } from 'theme/main';

function MyApp({ Component, pageProps }: AppProps): ReactElement {
    const [isLoaded, setIsLoaded] = useState(false);

    useEffect(() => {
        i18n.on('initialized', () => {
            setIsLoaded(true);
        });
    }, []);

    return (
        <ShopsysGlobalProvider theme={theme}>
            {isLoaded && (
                <I18nextProvider i18n={i18n}>
                    <Component {...pageProps} />
                </I18nextProvider>
            )}
        </ShopsysGlobalProvider>
    );
}

export default MyApp;
