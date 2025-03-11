'use client';

import { UserConsentForm } from 'app/_components/Blocks/UserConsent/UserConsentForm';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useTranslation } from 'components/providers/TranslationProvider';
import { useRouter } from 'next/navigation';
import { useCallback } from 'react';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const UserConsentContent: FC = () => {
    const { t } = useTranslation();
    const { push } = useRouter();

    const onSetCallback = useCallback(() => {
        showSuccessMessage(t('Your preferences have been set.'));
        push('/');
    }, [push, t]);

    return (
        <SimpleLayout heading={t('User consent')}>
            <UserConsentForm onSetCallback={onSetCallback} />
        </SimpleLayout>
    );
};
