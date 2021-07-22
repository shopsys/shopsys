import { useEffect, useState } from 'react';
import i18n from '../config/i18n';
import { I18nextProvider } from 'react-i18next';
import SsfwGlobalProvider from '../components/SsfwGlobalProvider';
import { theme } from '../theme/main';

function MyApp({ Component, pageProps }) {
    const [isLoaded, setIsLoaded] = useState(false);

    useEffect(() => {
        i18n.on('initialized', () => {
            setIsLoaded(true);
        });
    }, []);

    return (
        <SsfwGlobalProvider theme={theme}>
            {isLoaded && (
                <I18nextProvider i18n={i18n}>
                    <Component {...pageProps} />
                </I18nextProvider>
            )}
        </SsfwGlobalProvider>
    );
}

export default MyApp;
