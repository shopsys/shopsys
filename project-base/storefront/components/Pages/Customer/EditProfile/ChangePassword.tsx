import { Button } from 'components/Forms/Button/Button';
import { FormHeading, FormBlockWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { useCustomerChangeProfileFormMeta } from 'components/Pages/Customer/customerChangeProfileFormMeta';
import { usePasswordRecoveryMutation } from 'graphql/requests/passwordRecovery/mutations/PasswordRecoveryMutation.generated';
import { GtmFormType } from 'gtm/enums/GtmFormType';
import { onGtmSendFormEventHandler } from 'gtm/handlers/onGtmSendFormEventHandler';
import useTranslation from 'next-translate/useTranslation';
import { useFormContext } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type ChangePasswordProps = {
    email: string;
    hasPasswordSet: boolean;
};
export const ChangePassword: FC<ChangePasswordProps> = ({ email, hasPasswordSet }) => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<CustomerChangeProfileFormType>();
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);
    const [, resetPassword] = usePasswordRecoveryMutation();

    const onResetPasswordHandler = async () => {
        const resetPasswordResult = await resetPassword({ email: email });

        if (resetPasswordResult.data?.RequestPasswordRecovery !== undefined) {
            showSuccessMessage(t('We sent an email with further steps to your address'));
            onGtmSendFormEventHandler(GtmFormType.forgotten_password);
        }
    };

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Change password')}</FormHeading>
            {hasPasswordSet ? (
                <>
                    <PasswordInputControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.oldPassword.name}
                        passwordInputProps={{
                            label: formMeta.fields.oldPassword.label,
                            autoComplete: 'current-password',
                        }}
                        render={(passwordInput) => (
                            <FormColumn>
                                <FormLine bottomGap>{passwordInput}</FormLine>
                            </FormColumn>
                        )}
                    />
                    <FormColumn>
                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.newPassword.name}
                            render={(passwordInput) => <FormLine bottomGap>{passwordInput}</FormLine>}
                            passwordInputProps={{
                                label: formMeta.fields.newPassword.label,
                                autoComplete: 'new-password',
                            }}
                        />
                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.newPasswordConfirm.name}
                            render={(passwordInput) => <FormLine bottomGap>{passwordInput}</FormLine>}
                            passwordInputProps={{
                                label: formMeta.fields.newPasswordConfirm.label,
                                autoComplete: 'new-password-confirm',
                            }}
                        />
                    </FormColumn>
                </>
            ) : (
                <Button size="small" onClick={onResetPasswordHandler}>
                    {t('Send me a link to set a new password')}
                </Button>
            )}
        </FormBlockWrapper>
    );
};
