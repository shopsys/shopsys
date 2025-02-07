import { AppConfigProvider } from './AppConfigProvider';
import AuthProvider from './AuthProvider';
import BroadcastChannelProvider from './BroadcastChannelProvider';
import { CookiesStoreProvider } from './CookiesStoreProvider';
import { CookiesStoreSync } from './CookiesStoreSync';
import { DomainConfigProvider } from './DomainConfigProvider';
import ToastifyProvider from './ToastifyProvider';
import { TranslationProvider } from './TranslationProvider';
import { STATIC_REWRITE_PATHS } from 'app/_config/staticRewritePaths';
import { getIsUserLoggedInQuery } from 'app/_queries/getIsUserLoggedInQuery';
import { getSettingsQuery } from 'app/_queries/getSettingsQuery';
import { getDomainConfigServer } from 'app/_utils/domain/domainConfigServer';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { headers } from 'next/headers';
import { getDictionary } from 'utils/getDictionary';

type ProvidersProps = {
    children: React.ReactNode;
};

export default async function Providers({ children }: ProvidersProps) {
    const cookieStoreStateFromServer = getCookieStoreStateFromServer();
    const domainConfig = getDomainConfigServer(headers().get('host')!);
    const { defaultLocale: lang } = domainConfig;
    const dictionary = await getDictionary(lang);
    const [isUserLoggedIn, settingsData] = await Promise.allSettled([getIsUserLoggedInQuery(), getSettingsQuery()]);

    if (settingsData.status === 'rejected' || !settingsData.value?.settings) {
        throw new Error('Failed to fetch settings');
    }

    return (
        <CookiesStoreProvider cookieStoreStateFromServer={cookieStoreStateFromServer}>
            <DomainConfigProvider domainConfig={domainConfig}>
                <AppConfigProvider
                    domainConfig={domainConfig}
                    settings={settingsData.value.settings}
                    staticRewritePaths={STATIC_REWRITE_PATHS[domainConfig.url]}
                >
                    <TranslationProvider dictionary={dictionary} lang={lang}>
                        <AuthProvider
                            isUserLoggedIn={isUserLoggedIn.status === 'fulfilled' ? isUserLoggedIn.value : null}
                        >
                            <html lang={lang}>
                                <head>
                                    <script async src="https://unpkg.com/react-scan/dist/auto.global.js" />
                                </head>
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
                </AppConfigProvider>
            </DomainConfigProvider>
        </CookiesStoreProvider>
    );
}
