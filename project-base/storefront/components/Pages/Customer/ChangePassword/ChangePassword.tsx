import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import {
    useChangePasswordForm,
    useChangePasswordFormMeta,
} from 'components/Pages/Customer/ChangePassword/changePasswordFormMeta';
import { useChangePasswordMutation } from 'graphql/requests/customer/mutations/ChangePasswordMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { SubmitHandler, FormProvider } from 'react-hook-form';
import { CurrentCustomerType } from 'types/customer';
import { ChangePasswordFormType } from 'types/form';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { clearForm } from 'utils/forms/clearForm';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type ChangePasswordProps = {
    currentCustomerUser: CurrentCustomerType;
};

export const ChangePassword: FC<ChangePasswordProps> = ({ currentCustomerUser }) => {
    const { t } = useTranslation();
    const [, changePassword] = useChangePasswordMutation();
    const [formProviderMethods, defaultValues] = useChangePasswordForm({
        ...currentCustomerUser,
    });
    const formMeta = useChangePasswordFormMeta(formProviderMethods);
    const isSubmitting = formProviderMethods.formState.isSubmitting;

    const onSubmitChangePasswordFormHandler: SubmitHandler<ChangePasswordFormType> = async (
        changePasswordFormData,
        event,
    ) => {
        event?.preventDefault();

        onChangePasswordHandler(changePasswordFormData);
    };

    const onChangePasswordHandler = async (changePasswordFormData: ChangePasswordFormType) => {
        if (changePasswordFormData.newPassword === '' || changePasswordFormData.newPasswordConfirm === '') {
            return;
        }

        const changePasswordResult = await changePassword({
            email: currentCustomerUser.email,
            oldPassword: changePasswordFormData.oldPassword,
            newPassword: changePasswordFormData.newPassword,
        });

        if (changePasswordResult.data?.ChangePassword !== undefined) {
            showSuccessMessage(formMeta.messages.success);
            clearForm(changePasswordResult.error, formProviderMethods, defaultValues);
        }

        if (changePasswordResult.error !== undefined) {
            const { applicationError } = getUserFriendlyErrors(changePasswordResult.error, t);

            if (applicationError !== undefined) {
                if (applicationError.type === 'invalid-account-or-password') {
                    formProviderMethods.setError('oldPassword', { message: t('The current password is incorrect') });
                }
            } else {
                showErrorMessage(formMeta.messages.error, GtmMessageOriginType.other);
            }
        }
    };

    return (
        <>
            <p className="sr-only" id="change-password-form-description">
                {t(
                    'Change password form for changing your password. Please fill in your current password and your new password.',
                )}
            </p>

            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onSubmitChangePasswordFormHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper className="border-b-0">
                            <PasswordInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.oldPassword.name}
                                passwordInputProps={{
                                    label: formMeta.fields.oldPassword.label,
                                    autoComplete: 'current-password',
                                    'aria-labelledby': 'change-password-form-description',
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
                        </FormBlockWrapper>

                        <FormButtonWrapper className="mt-0 pb-6">
                            <SubmitButton
                                aria-label={t('Submit form to change your password')}
                                isDisabled={isSubmitting}
                            >
                                {t('Change password')}
                            </SubmitButton>
                        </FormButtonWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </>
    );
};
