import { AppConfigProvider } from './AppConfigProvider';
import { AuthInfo } from './AuthInfo';
import { AuthProvider } from './AuthProvider';
import { AuthorizationProvider } from './AuthorizationProvider';
import BroadcastChannelProvider from './BroadcastChannelProvider';
import { CookiesStoreProvider } from './CookiesStoreProvider';
import { CookiesStoreSync } from './CookiesStoreSync';
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
import { SkeletonLayout } from 'components/Blocks/Skeleton/SkeletonLayout';
import { LazyMotion, MotionConfig } from 'framer-motion';
import { headers } from 'next/headers';
import { Suspense } from 'react';
import framerMotionPlugins from 'utils/animations/framerMotionPlugins';
import { getDictionary } from 'utils/getDictionary';

type ProvidersProps = {
    children: React.ReactNode;
};

// Deferred providers for non-critical data
async function DeferredProviders({ children }: { children: React.ReactNode }) {
    // Use Promise.all for better performance when all are needed
    // and handle errors more gracefully
    try {
        console.time('⏱️ Providers promises data loading');
        const userPromise = getCurrentCustomerData();
        const initialProductListStatePromise = getInitialProductListState();
        const customerUserRolesPromise = getCurrentCustomerUserRoles();
        console.timeEnd('⏱️ Providers promises data loading');

        return (
            <AuthProvider user={await userPromise}>
                <AuthorizationProvider customerUserRoles={await customerUserRolesPromise}>
                    <MotionConfig reducedMotion="user">
                        <LazyMotion features={framerMotionPlugins}>
                            <ProductListProvider initialState={await initialProductListStatePromise}>
                                <ToastifyProvider>
                                    <AuthInfo isUserLoggedIn={!!(await userPromise)} />
                                    <CookiesStoreSync />
                                    <BroadcastChannelProvider />
                                    {children}
                                    <Portal />
                                </ToastifyProvider>
                            </ProductListProvider>
                        </LazyMotion>
                    </MotionConfig>
                </AuthorizationProvider>
            </AuthProvider>
        );
    } catch {
        // Fallback for critical failures
        return (
            <AuthProvider user={undefined}>
                <AuthorizationProvider customerUserRoles={[]}>
                    <MotionConfig reducedMotion="user">
                        <LazyMotion features={framerMotionPlugins}>
                            <ProductListProvider initialState={{}}>
                                <ToastifyProvider>
                                    <AuthInfo isUserLoggedIn={false} />
                                    <CookiesStoreSync />
                                    <BroadcastChannelProvider />
                                    {children}
                                    <Portal />
                                </ToastifyProvider>
                            </ProductListProvider>
                        </LazyMotion>
                    </MotionConfig>
                </AuthorizationProvider>
            </AuthProvider>
        );
    }
}

export default async function Providers({ children }: ProvidersProps) {
    const cookieStoreStateFromServerPromise = getCookieStoreStateFromServer();
    const domainConfig = getDomainConfig((await headers()).get('host')!);
    const { defaultLocale: lang } = domainConfig;

    // Use Promise.all for critical data since both are required
    const settingsPromise = getSettingsQuery();
    const dictionaryPromise = getDictionary(lang);

    return (
        <CookiesStoreProvider cookieStoreStateFromServer={await cookieStoreStateFromServerPromise}>
            <AppConfigProvider
                domainConfig={domainConfig}
                settings={(await settingsPromise).data?.settings}
                staticRewritePaths={STATIC_REWRITE_PATHS[domainConfig.url]}
            >
                <TranslationProvider dictionary={await dictionaryPromise} lang={lang}>
                    <html lang={lang}>
                        {/* suppressHydrationWarning for ignoring grammarly extension */}
                        <body suppressHydrationWarning>
                            <Suspense fallback={<SkeletonLayout />}>
                                <DeferredProviders>{children}</DeferredProviders>
                            </Suspense>
                        </body>
                    </html>
                </TranslationProvider>
            </AppConfigProvider>
        </CookiesStoreProvider>
    );
}
