'use client';

import { useHandleActionsAfterLogout } from './useHandleActionsAfterLogout';
import { logoutAction } from 'app/_actions/logoutAction';
import { useTranslation } from 'components/providers/TranslationProvider';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

export const useLogout = () => {
    const { t } = useTranslation();

    const handleActionsAfterLogout = useHandleActionsAfterLogout();

    const handleLogout = async () => {
        const { error } = await logoutAction();

        if (error) {
            showErrorMessage(t('An error occurred while logging out'));
            return;
        }

        handleActionsAfterLogout();
    };

    return handleLogout;
};
