import { showInfoMessage, showSuccessMessage } from 'components/Helpers/toasts';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffect } from 'react';
import { usePersistStore } from 'store/zustand/usePersistStore';

export const useLoginLoader = () => {
    const t = useTypedTranslationFunction();
    const loginLoading = usePersistStore((store) => store.loginLoading);
    const updateGeneralState = usePersistStore((store) => store.updateLoginLoadingState);

    useEffect(() => {
        if (!loginLoading) {
            return;
        }

        showSuccessMessage(t('Successfully logged in'));

        if (loginLoading === 'loading-with-cart-modifications') {
            showInfoMessage(t('Your cart has been modified. Please check the changes.'));
        }

        updateGeneralState(null);
    }, []);

    return null;
};
