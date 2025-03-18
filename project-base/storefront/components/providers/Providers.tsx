import { AppConfigProvider } from './AppConfigProvider';
import { AuthInfo } from './AuthInfo';
import { AuthProvider } from './AuthProvider';
import { AuthorizationProvider } from './AuthorizationProvider';
import BroadcastChannelProvider from './BroadcastChannelProvider';
import { CookiesStoreProvider } from './CookiesStoreProvider';
import { CookiesStoreSync } from './CookiesStoreSync';
import { DomainConfigProvider } from './DomainConfigProvider';
import { ProductListProvider } from './ProductListProvider';
import ToastifyProvider from './ToastifyProvider';
import { TranslationProvider } from './TranslationProvider';
import { STATIC_REWRITE_PATHS } from 'app/_config/staticRewritePaths';
import { getCurrentCustomerData } from 'app/_queries/getCurrentCustomerData';
import { getCurrentCustomerUserRoles } from 'app/_queries/getCurrentCustomerUserRoles';
import { getSettingsQuery } from 'app/_queries/getSettingsQuery';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { getDomainConfig } from 'app/_utils/getDomainConfig';
import { getInitialProductListState } from 'app/_utils/getInitalProductListState';
import { Portal } from 'components/Basic/Portal/Portal';
import { headers } from 'next/headers';
import { getDictionary } from 'utils/getDictionary';

type ProvidersProps = {
    children: React.ReactNode;
};

export default async function Providers({ children }: ProvidersProps) {
    const cookieStoreStateFromServer = await getCookieStoreStateFromServer();
    const domainConfig = getDomainConfig((await headers()).get('host')!);
    const { defaultLocale: lang } = domainConfig;
    const dictionary = await getDictionary(lang);
    const [user, settingsData, initialState] = await Promise.allSettled([
        getCurrentCustomerData(),
        getSettingsQuery(),
        getInitialProductListState(),
    ]);

    const customerUserRoles = await getCurrentCustomerUserRoles();

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
                        <AuthProvider user={user.status === 'fulfilled' ? user.value : undefined}>
                            <AuthorizationProvider customerUserRoles={customerUserRoles}>
                                <ProductListProvider
                                    initialState={initialState.status === 'fulfilled' ? initialState.value : {}}
                                >
                                    <html lang={lang}>
                                        {/* <head>
                                            <script async src="https://unpkg.com/react-scan/dist/auto.global.js" />
                                        </head> */}
                                        {/* suppressHydrationWarning for ignoring grammarly extension */}
                                        <body suppressHydrationWarning>
                                            <AuthInfo isUserLoggedIn={user.status === 'fulfilled' && !!user.value} />
                                            <CookiesStoreSync />
                                            <BroadcastChannelProvider />
                                            {children}
                                            <Portal />
                                            <ToastifyProvider />
                                        </body>
                                    </html>
                                </ProductListProvider>
                            </AuthorizationProvider>
                        </AuthProvider>
                    </TranslationProvider>
                </AppConfigProvider>
            </DomainConfigProvider>
        </CookiesStoreProvider>
    );
}
