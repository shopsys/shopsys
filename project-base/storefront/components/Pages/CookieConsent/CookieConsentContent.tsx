import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useCallback } from 'react';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const CookieConsentContent: FC = () => {
    const { t } = useTranslation();
    const { push } = useRouter();

    const onSetCallback = useCallback(() => {
        showSuccessMessage(t('Your cookie preferences have been set.'));
        push('/');
    }, [push, t]);

    return (
        <SimpleLayout heading={t('Cookie consent')}>
            <UserConsentForm onSetCallback={onSetCallback} />
        </SimpleLayout>
    );
};
