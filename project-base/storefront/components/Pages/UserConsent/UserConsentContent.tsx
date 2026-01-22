import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useRouter } from 'next/router';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const UserConsentContent: FC = () => {
    const { t } = useTranslation();
    const { push } = useRouter();

    const onSetCallback = () => {
        showSuccessMessage(t('Your preferences have been set.'));
        push('/');
    };

    return (
        <Webline width="lg">
            <VerticalStack gap="sm">
                <h1>{t('User consent')}</h1>

                <UserConsentForm onSetCallback={onSetCallback} />
            </VerticalStack>
        </Webline>
    );
};
