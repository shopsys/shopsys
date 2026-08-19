import { LockCheckIcon } from 'components/Basic/Icon/LockCheckIcon';
import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import { UserConsentPolicyLink } from 'components/Blocks/UserConsent/UserConsentPolicyLink';
import { PageHero } from 'components/Layout/PageHero/PageHero';
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
                <PageHero description={<UserConsentPolicyLink />} icon={LockCheckIcon} title={t('User consent')} />

                <UserConsentForm onSetCallback={onSetCallback} />
            </VerticalStack>
        </Webline>
    );
};
