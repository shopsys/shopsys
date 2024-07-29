import { useRecoveryPasswordForm, useRecoveryPasswordFormMeta } from './recoveryPasswordFormMeta';
import { Link } from 'components/Basic/Link/Link';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRecoverPasswordMutation } from 'graphql/requests/passwordRecovery/mutations/RecoverPasswordMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { useCallback, useEffect } from 'react';
import { FormProvider, SubmitHandler, useController } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { NewPasswordFormType } from 'types/form';
import { useLogin } from 'utils/auth/useLogin';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type NewPasswordContentProps = {
    hash: string;
    email: string;
};

export const NewPasswordContent: FC<NewPasswordContentProps> = ({ email, hash }) => {
    const { t } = useTranslation();
    const [, newPassword] = useRecoverPasswordMutation();
    const { url } = useDomainConfig();
    const [resetPasswordUrl] = getInternationalizedStaticUrls(['/reset-password'], url);
    const [formProviderMethods] = useRecoveryPasswordForm();
    const formMeta = useRecoveryPasswordFormMeta(formProviderMethods);
    const login = useLogin();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const {
        fieldState: { invalid: isNewPasswordInvalid },
        field: { value: newPasswordValue },
    } = useController({ name: formMeta.fields.newPasswordConfirm.name, control: formProviderMethods.control });

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

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

    useEffect(() => {
        if (hash === '' || email === '') {
            showErrorMessage(t('An error occurred while loading form data'));
        }
    }, []);

    if (hash === '' || email === '') {
        return (
            <SimpleLayout heading={t('Set new password')}>
                <Trans
                    defaultTrans="An error occurred while loading form data. <0/> Please try to resend new password recovery link <lnk1>on this page</lnk1>."
                    i18nKey="ResendRecoveryLink"
                    components={{
                        0: <br />,
                        lnk1: <Link href={resetPasswordUrl} />,
                    }}
                />
            </SimpleLayout>
        );
    }

    return (
        <Webline className="flex flex-col items-center">
            <h1 className="max-w-3xl w-full">{t('Login')}</h1>
            <FormProvider {...formProviderMethods}>
                <Form
                    className="w-full flex justify-center"
                    onSubmit={formProviderMethods.handleSubmit(onNewPasswordHandler)}
                >
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            <PasswordInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.newPassword.name}
                                render={(passwordInput) => <FormLine bottomGap>{passwordInput}</FormLine>}
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
                            <FormButtonWrapper>
                                <SubmitButton
                                    isWithDisabledLook={isNewPasswordInvalid || newPasswordValue.length === 0}
                                >
                                    {t('Set new password')}
                                </SubmitButton>
                            </FormButtonWrapper>
                        </FormBlockWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Webline>
    );
};
