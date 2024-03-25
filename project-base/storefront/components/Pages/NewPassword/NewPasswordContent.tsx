import { useRecoveryPasswordForm, useRecoveryPasswordFormMeta } from './recoveryPasswordFormMeta';
import { Link } from 'components/Basic/Link/Link';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRecoverPasswordMutation } from 'graphql/requests/passwordRecovery/mutations/RecoverPasswordMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { getInternationalizedStaticUrls } from 'helpers/staticUrls/getInternationalizedStaticUrls';
import { showErrorMessage } from 'helpers/toasts/showErrorMessage';
import { showSuccessMessage } from 'helpers/toasts/showSuccessMessage';
import { useAuth } from 'hooks/auth/useAuth';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useCallback, useEffect } from 'react';
import { FormProvider, SubmitHandler, useController } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { NewPasswordFormType } from 'types/form';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

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
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const { login } = useAuth();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const {
        fieldState: { invalid: isNewPasswordInvalid },
        field: { value: newPasswordValue },
    } = useController({ name: formMeta.fields.newPasswordAgain.name, control: formProviderMethods.control });

    const onNewPasswordHandler = useCallback<SubmitHandler<NewPasswordFormType>>(
        async (data) => {
            const formData = {
                hash: hash,
                email: email,
                newPassword: data.newPassword,
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
            showErrorMessage(t('Error occured while loading form data'));
        }
    }, []);

    if (hash === '' || email === '') {
        return (
            <SimpleLayout heading={t('Set new password')}>
                <Trans
                    defaultTrans="Error occured while loading form data. <0/> Please try to resend new password recovery link <lnk1>on this page</lnk1>."
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
        <>
            <SimpleLayout heading={t('Set new password')}>
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onNewPasswordHandler)}>
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
                            name={formMeta.fields.newPasswordAgain.name}
                            render={(passwordInput) => <FormLine>{passwordInput}</FormLine>}
                            passwordInputProps={{
                                label: formMeta.fields.newPasswordAgain.label,
                            }}
                        />
                        <div className="mt-8 flex w-full justify-between">
                            <SubmitButton isWithDisabledLook={isNewPasswordInvalid || newPasswordValue.length === 0}>
                                {t('Set new password')}
                            </SubmitButton>
                        </div>
                    </Form>
                </FormProvider>
            </SimpleLayout>
            {isErrorPopupVisible && (
                <ErrorPopup
                    fields={formMeta.fields}
                    gtmMessageOrigin={GtmMessageOriginType.other}
                    onCloseCallback={() => setErrorPopupVisibility(false)}
                />
            )}
        </>
    );
};
