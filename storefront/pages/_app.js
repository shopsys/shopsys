import { I18nextProvider } from 'react-i18next';
import i18n from '../config/i18n';
import { useState, useEffect } from 'react';

function MyApp({ Component, pageProps }) {
    const [isLoaded, setIsLoaded] = useState(false);

    useEffect(() => {
        i18n.on('initialized', () => {
            setIsLoaded(true);
        });
    }, []);

    return (
        <>
            {isLoaded && (
                <I18nextProvider i18n={i18n}>
                    <Component {...pageProps} />
                </I18nextProvider>
            )}
        </>
    );
}

export default MyApp;
