import AuthProvider from './AuthProvider';
import BroadcastChannelProvider from './BroadcastChannelProvider';
import { CookiesStoreProvider } from './CookiesStoreProvider';
import { CookiesStoreSync } from './CookiesStoreSync';
import { DomainConfigProvider } from './DomainConfigProvider';
import { SettingsProvider } from './SettingsProvider';
import ToastifyProvider from './ToastifyProvider';
import { TranslationProvider } from './TranslationProvider';
import { getIsUserLoggedInQuery } from 'app/_queries/getIsUserLoggedInQuery';
import { getSettingsQuery } from 'app/_queries/getSettingsQuery';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { headers } from 'next/headers';
import { getDomainConfig } from 'utils/domain/domainConfig';
import { getDictionary } from 'utils/getDictionary';

type ProvidersProps = {
    children: React.ReactNode;
};

export default async function Providers({ children }: ProvidersProps) {
    const cookieStoreStateFromServer = getCookieStoreStateFromServer();
    const domainConfig = getDomainConfig(headers().get('host')!);
    const { defaultLocale: lang } = domainConfig;
    const dictionary = await getDictionary(lang);
    const [isUserLoggedIn, settings] = await Promise.all([getIsUserLoggedInQuery(), getSettingsQuery()]);

    return (
        <CookiesStoreProvider cookieStoreStateFromServer={cookieStoreStateFromServer}>
            <DomainConfigProvider domainConfig={domainConfig}>
                <SettingsProvider settings={settings}>
                    <TranslationProvider dictionary={dictionary} lang={lang}>
                        <AuthProvider isUserLoggedIn={isUserLoggedIn}>
                            <html lang={lang}>
                                {/* suppressHydrationWarning for ignoring grammarly extension */}
                                <body suppressHydrationWarning>
                                    <CookiesStoreSync />
                                    <BroadcastChannelProvider />
                                    {children}
                                    <ToastifyProvider />
                                </body>
                            </html>
                        </AuthProvider>
                    </TranslationProvider>
                </SettingsProvider>
            </DomainConfigProvider>
        </CookiesStoreProvider>
    );
}
