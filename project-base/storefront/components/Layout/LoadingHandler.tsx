import { showInfoMessage, showSuccessMessage } from 'components/Helpers/toasts';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffect } from 'react';
import { usePersistStore } from 'store/zustand/usePersistStore';

export const LoadingHandler: FC = () => {
    const t = useTypedTranslationFunction();
    const loginLoading = usePersistStore((s) => s.loginLoading);
    const updateGeneralState = usePersistStore((s) => s.updateLoginLoadingState);

    useEffect(() => {
        if (loginLoading === 'not-loading') {
            return;
        }

        showSuccessMessage(t('Successfully logged in'));

        if (loginLoading === 'loading-with-cart-modifications') {
            showInfoMessage(t('Your cart has been modified. Please check the changes.'));
        }

        updateGeneralState({ loginLoading: 'not-loading' });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return null;
};
