import { showInfoMessage, showSuccessMessage } from 'helpers/toasts';
import useTranslation from 'next-translate/useTranslation';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useLoginLoader = () => {
    const { t } = useTranslation();
    const loginLoading = usePersistStore((store) => store.loginLoading);
    const updateLoginLoadingState = usePersistStore((store) => store.updateLoginLoadingState);

    useEffect(() => {
        if (!loginLoading) {
            return;
        }

        showSuccessMessage(t('Successfully logged in'));

        if (loginLoading === 'loading-with-cart-modifications') {
            showInfoMessage(t('Your cart has been modified. Please check the changes.'));
        }

        updateLoginLoadingState(null);
    }, []);
};
