import { useRecoveryPasswordForm, useRecoveryPasswordFormMeta } from './formMeta';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/toasts';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { BreadcrumbFragmentApi, useRecoverPasswordMutationApi } from 'graphql/generated';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useDomainConfig } from 'hooks/useDomainConfig';
import Trans from 'next-translate/Trans';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler, useController } from 'react-hook-form';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { NewPasswordFormType } from 'types/form';
import { GtmMessageOriginType } from 'types/gtm/enums';

type NewPasswordContentProps = {
    hash: string;
    email: string;
    breadcrumbs: BreadcrumbFragmentApi[];
};

export const NewPasswordContent: FC<NewPasswordContentProps> = ({ breadcrumbs, email, hash }) => {
    const t = useTypedTranslationFunction();
    const [, newPassword] = useRecoverPasswordMutationApi();
    const { url } = useDomainConfig();
    const [newPasswordUrl, resetPasswordUrl] = getInternationalizedStaticUrls(
        ['/new-password', '/reset-password'],
        url,
    );
    const [formProviderMethods] = useRecoveryPasswordForm();
    const formMeta = useRecoveryPasswordFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const { login } = useAuth();
    const cartUuid = usePersistStore((s) => s.cartUuid);
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

    useEffectOnce(() => {
        if (hash === '' || email === '') {
            showErrorMessage(t('Error occured while loading form data'));
        }
    });

    if (hash === '' || email === '') {
        return (
            <SimpleLayout heading={t('Set new password')} breadcrumb={breadcrumbs}>
                <Trans
                    i18nKey="ResendRecoveryLink"
                    defaultTrans="Error occured while loading form data. <0/> Please try to resend new password recovery link <lnk1>on this page</lnk1>."
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
            <SimpleLayout
                heading={t('Set new password')}
                breadcrumb={[{ __typename: 'Link', name: t('Set new password'), slug: newPasswordUrl }]}
            >
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onNewPasswordHandler)}>
                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            name={formMeta.fields.newPassword.name}
                            render={(passwordInput) => <FormLine bottomGap>{passwordInput}</FormLine>}
                            formName={formMeta.formName}
                            passwordInputProps={{
                                label: formMeta.fields.newPassword.label,
                            }}
                        />
                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            name={formMeta.fields.newPasswordAgain.name}
                            render={(passwordInput) => <FormLine>{passwordInput}</FormLine>}
                            formName={formMeta.formName}
                            passwordInputProps={{
                                label: formMeta.fields.newPasswordAgain.label,
                            }}
                        />
                        <div className="mt-8 flex w-full justify-between">
                            <Button
                                type="submit"
                                isWithDisabledLook={isNewPasswordInvalid || newPasswordValue.length === 0}
                            >
                                {t('Set new password')}
                            </Button>
                        </div>
                    </Form>
                </FormProvider>
            </SimpleLayout>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
                gtmMessageOrigin={GtmMessageOriginType.other}
            />
        </>
    );
};
