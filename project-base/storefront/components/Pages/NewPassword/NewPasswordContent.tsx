import { useRecoveryPasswordForm, useRecoveryPasswordFormMeta } from './recoveryPasswordFormMeta';
import { LockCheckIcon } from 'components/Basic/Icon/LockCheckIcon';
import { LockCrossIcon } from 'components/Basic/Icon/LockCrossIcon';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRecoverPasswordMutation } from 'graphql/requests/passwordRecovery/mutations/RecoverPasswordMutation.generated';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler, useController } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { NewPasswordFormType } from 'types/form';
import { useLogin } from 'utils/auth/useLogin';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type NewPasswordContentProps = {
    hash: string;
    email: string;
};

export const NewPasswordContent: FC<NewPasswordContentProps> = ({ email, hash }) => {
    const { t } = useTranslation();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const [, newPassword] = useRecoverPasswordMutation();
    const { url } = useDomainConfig();
    const [resetPasswordUrl] = getInternationalizedStaticUrls(['/reset-password'], url);
    const [formProviderMethods] = useRecoveryPasswordForm();
    const formMeta = useRecoveryPasswordFormMeta(formProviderMethods);
    const login = useLogin();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const {
        fieldState: { error },
        field: { value: newPasswordValue },
    } = useController({ name: formMeta.fields.newPasswordConfirm.name, control: formProviderMethods.control });

    const onNewPasswordHandler = useCallback<SubmitHandler<NewPasswordFormType>>(
        async (newPasswordFormData) => {
            const formData = {
                hash: hash,
                email: email,
                newPassword: newPasswordFormData.newPassword,
            };
            const newPasswordResult = await newPassword(formData);

            if (newPasswordResult.data?.RecoverPassword.tokens.accessToken !== undefined) {
                showSuccessMessage(formMeta.messages.success);

                login(
                    {
                        email: email,
                        password: formProviderMethods.getValues('newPassword'),
                        previousCartUuid: cartUuid,
                    },
                    '/',
                );
                updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });
            }

            handleFormErrors(newPasswordResult.error, formProviderMethods, t, formMeta.messages.error, formMeta.fields);
        },
        [
            cartUuid,
            email,
            formMeta.fields,
            formMeta.messages.error,
            formMeta.messages.success,
            formProviderMethods,
            hash,
            login,
            newPassword,
            t,
        ],
    );

    if (hash === '' || email === '') {
        return (
            <Webline width="lg">
                <VerticalStack gap="sm">
                    <PageHero
                        actionHref={resetPasswordUrl}
                        actionSkeletonType="reset-password"
                        actionTitle={t('Resend new password recovery link')}
                        description={t('Unable to load form data. Request a new link to set your password again.')}
                        icon={LockCrossIcon}
                        title={t('Set new password')}
                    />
                </VerticalStack>
            </Webline>
        );
    }

    return (
        <Webline width="lg">
            <VerticalStack gap="sm">
                <PageHero
                    icon={LockCheckIcon}
                    title={t('Set new password')}
                    description={t(
                        'Set new password form for setting your new password. Please fill in your new password and confirm it.',
                    )}
                />
                <FormProvider {...formProviderMethods}>
                    <Form
                        className="flex w-full justify-center"
                        onSubmit={formProviderMethods.handleSubmit(onNewPasswordHandler)}
                    >
                        <FormContentWrapper>
                            <FormBlockWrapper>
                                <PasswordInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.newPassword.name}
                                    render={(passwordInput) => <FormLine>{passwordInput}</FormLine>}
                                    passwordInputProps={{
                                        label: formMeta.fields.newPassword.label,
                                    }}
                                />

                                <PasswordInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.newPasswordConfirm.name}
                                    render={(passwordInput) => <FormLine>{passwordInput}</FormLine>}
                                    passwordInputProps={{
                                        label: formMeta.fields.newPasswordConfirm.label,
                                    }}
                                />
                            </FormBlockWrapper>

                            <FormButtonWrapper>
                                <SubmitButton
                                    aria-label={t('Submit form to set your new password', { ns: 'accessibility' })}
                                    hasDisabledCursor={!!error || newPasswordValue.length === 0}
                                >
                                    {t('Set new password')}
                                </SubmitButton>
                            </FormButtonWrapper>
                        </FormContentWrapper>
                    </Form>
                </FormProvider>
            </VerticalStack>
        </Webline>
    );
};
