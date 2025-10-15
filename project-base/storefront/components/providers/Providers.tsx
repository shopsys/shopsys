import { AppConfigProvider } from './AppConfigProvider';
import { AuthInfo } from './AuthInfo';
import { AuthProvider } from './AuthProvider';
import { AuthorizationProvider } from './AuthorizationProvider';
import BroadcastChannelProvider from './BroadcastChannelProvider';
import { CookiesStoreProvider } from './CookiesStoreProvider';
import { CookiesStoreSync } from './CookiesStoreSync';
import { DomainConfigProvider } from './DomainConfigProvider';
import { ProductListProvider, ProductListState } from './ProductListProvider';
import ToastifyProvider from './ToastifyProvider';
import { TranslationProvider } from './TranslationProvider';
import { STATIC_REWRITE_PATHS } from 'app/_config/staticRewritePaths';
import { getCurrentCustomerData } from 'app/_queries/getCurrentCustomerData';
import { getCurrentCustomerUserRoles } from 'app/_queries/getCurrentCustomerUserRoles';
import { getSettingsQuery } from 'app/_queries/getSettingsQuery';
import { CookiesStoreState, getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { DomainConfigType, getDomainConfig } from 'app/_utils/getDomainConfig';
import { getInitialProductListState } from 'app/_utils/getInitalProductListState';
import { Portal } from 'components/Basic/Portal/Portal';
import { LazyMotion, MotionConfig } from 'framer-motion';
import { TypeSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.ssr';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { Locale } from 'i18n-config';
import { headers } from 'next/headers';
import { use } from 'react';
import { CurrentCustomerType } from 'types/customer';
import { Dictionary } from 'types/translation';
import framerMotionPlugins from 'utils/animations/framerMotionPlugins';
import { getDictionary } from 'utils/getDictionary';

type ProvidersProps = {
    children: React.ReactNode;
};

type DeferredProvidersClientProps = {
    children: React.ReactNode;
    cookieStoreStateFromServerPromise: Promise<CookiesStoreState>;
    domainConfig: DomainConfigType;
    settingsPromise: Promise<{ data: TypeSettingsQuery | undefined; error: any }>;
    dictionaryPromise: Promise<Dictionary>;
    lang: Locale;
    userPromise: Promise<CurrentCustomerType | undefined>;
    customerUserRolesPromise: Promise<TypeCustomerUserRoleEnum[]>;
    initialProductListStatePromise: Promise<ProductListState>;
};

export function ProvidersWrapper({
    children,
    cookieStoreStateFromServerPromise,
    domainConfig,
    settingsPromise,
    dictionaryPromise,
    lang,
    userPromise,
    customerUserRolesPromise,
    initialProductListStatePromise,
}: DeferredProvidersClientProps) {
    'use client';
    const cookieStoreStateFromServer = use(cookieStoreStateFromServerPromise);
    const settingsResult = use(settingsPromise);
    const dictionary = use(dictionaryPromise);
    const user = use(userPromise);
    const customerUserRoles = use(customerUserRolesPromise);
    const initialProductListState = use(initialProductListStatePromise);

    if (!settingsResult.data?.settings) {
        throw new Error('Failed to fetch settings - settings data is unavailable');
    }

    const staticRewritePaths = STATIC_REWRITE_PATHS[domainConfig.url];

    return (
        <CookiesStoreProvider cookieStoreStateFromServer={cookieStoreStateFromServer}>
            <DomainConfigProvider domainConfig={domainConfig}>
                <AppConfigProvider
                    domainConfig={domainConfig}
                    settings={settingsResult.data.settings}
                    staticRewritePaths={staticRewritePaths}
                >
                    <TranslationProvider dictionary={dictionary} lang={lang}>
                        <AuthProvider user={user}>
                            <AuthorizationProvider customerUserRoles={customerUserRoles}>
                                <ProductListProvider initialState={initialProductListState}>
                                    <MotionConfig reducedMotion="user">
                                        <LazyMotion features={framerMotionPlugins}>
                                            <html lang={lang}>
                                                {/* suppressHydrationWarning for ignoring grammarly extension */}
                                                <body suppressHydrationWarning>
                                                    <ToastifyProvider>
                                                        <AuthInfo isUserLoggedIn={!!user} />
                                                        <CookiesStoreSync />
                                                        <BroadcastChannelProvider />
                                                        {children}
                                                        <Portal />
                                                    </ToastifyProvider>
                                                </body>
                                            </html>
                                        </LazyMotion>
                                    </MotionConfig>
                                </ProductListProvider>
                            </AuthorizationProvider>
                        </AuthProvider>
                    </TranslationProvider>
                </AppConfigProvider>
            </DomainConfigProvider>
        </CookiesStoreProvider>
    );
}

export default async function Providers({ children }: ProvidersProps) {
    const domainConfig = getDomainConfig((await headers()).get('host')!);
    const { defaultLocale: lang } = domainConfig;

    const cookieStoreStateFromServerPromise = getCookieStoreStateFromServer();
    const userPromise = getCurrentCustomerData();
    const initialProductListStatePromise = getInitialProductListState();
    const customerUserRolesPromise = getCurrentCustomerUserRoles();
    const settingsPromise = getSettingsQuery();
    const dictionaryPromise = getDictionary(lang);

    return (
        <ProvidersWrapper
            cookieStoreStateFromServerPromise={cookieStoreStateFromServerPromise}
            customerUserRolesPromise={customerUserRolesPromise}
            dictionaryPromise={dictionaryPromise}
            domainConfig={domainConfig}
            initialProductListStatePromise={initialProductListStatePromise}
            lang={lang}
            settingsPromise={settingsPromise}
            userPromise={userPromise}
        >
            {children}
        </ProvidersWrapper>
    );
}
