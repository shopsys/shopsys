import { getCookies } from 'cookies-next';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { isClient } from 'utils/isClient';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const useAuthLoader = () => {
    const { t } = useTranslation();
    const authLoading = usePersistStore((store) => store.authLoading);

    const router = useRouter();
    const isUserLoggedIn = useIsUserLoggedIn();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);

    const isStoreHydrated = isClient && usePersistStore.persist.hasHydrated();

    useEffect(() => {
        const cookies = getCookies();
        const isWithUserTokens = !!(cookies.accessToken && cookies.refreshToken);

        if ((isUserLoggedIn && !isWithUserTokens) || (!isUserLoggedIn && isWithUserTokens)) {
            router.reload();
        }
    }, [slug]);

    useEffect(() => {
        if (authLoading && isStoreHydrated) {
            if (authLoading === 'logout-loading') {
                showSuccessMessage(t('Successfully logged out'));
            }

            if (
                authLoading === 'registration-loading' ||
                authLoading === 'registration-loading-with-cart-modifications'
            ) {
                showSuccessMessage(t('Your account has been created and you are logged in now'));
            }

            if (authLoading === 'login-loading' || authLoading === 'login-loading-with-cart-modifications') {
                showSuccessMessage(t('Successfully logged in'));
            }

            if (
                authLoading === 'registration-loading-with-cart-modifications' ||
                authLoading === 'login-loading-with-cart-modifications'
            ) {
                showInfoMessage(t('Your cart has been modified. Please check the changes.'));
            }

            updateAuthLoadingState(null);
        }
    }, [isStoreHydrated]);
};
