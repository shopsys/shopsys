import { getCookies } from 'cookies-next';
import { isClient } from 'helpers/isClient';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { showInfoMessage } from 'helpers/toasts/showInfoMessage';
import { showSuccessMessage } from 'helpers/toasts/showSuccessMessage';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';

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
