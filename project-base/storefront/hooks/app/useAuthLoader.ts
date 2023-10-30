import { isClient } from 'helpers/isClient';
import { showInfoMessage, showSuccessMessage } from 'helpers/toasts';
import useTranslation from 'next-translate/useTranslation';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useAuthLoader = () => {
    const { t } = useTranslation();
    const authLoading = usePersistStore((store) => store.authLoading);
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);

    const isStoreHydrated = isClient && usePersistStore.persist.hasHydrated();

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
