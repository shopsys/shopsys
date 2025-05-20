import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
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
        <Webline width="lg">
            <VerticalStack gap="sm">
                <h1>{t('User consent')}</h1>

                <UserConsentForm onSetCallback={onSetCallback} />
            </VerticalStack>
        </Webline>
    );
};
