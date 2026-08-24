import { Button } from 'components/Forms/Button/Button';
import { usePasswordRecoveryMutation } from 'graphql/requests/passwordRecovery/mutations/PasswordRecoveryMutation.generated';
import { GtmFormType } from 'gtm/enums/GtmFormType';
import { onGtmSendFormEventHandler } from 'gtm/handlers/onGtmSendFormEventHandler';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type ResetPasswordProps = {
    email: string;
};

export const ResetPassword: FC<ResetPasswordProps> = ({ email }) => {
    const { t } = useTranslation();
    const [{ fetching }, resetPassword] = usePasswordRecoveryMutation();
    const handleError = useErrorHandler();

    const onResetPasswordHandler = async () => {
        const resetPasswordResult = await resetPassword({ email: email });

        if (resetPasswordResult.data?.RequestPasswordRecovery) {
            showSuccessMessage(t('We sent an email with further steps to your address'));
            onGtmSendFormEventHandler(GtmFormType.forgotten_password);
        }

        handleError(resetPasswordResult.error);
    };

    return (
        <Button disabled={fetching} hasDisabledLook={fetching} size="small" onClick={onResetPasswordHandler}>
            {t('Send me a link to set a new password')}
        </Button>
    );
};
