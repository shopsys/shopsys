import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { useRecoveryPasswordForm, useRecoveryPasswordFormMeta } from './formMeta';
import Button from 'components/Forms/Button';
import { ButtonWrapperStyled } from './NewPassword.style';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import Link from 'components/Basic/Link';
import { NewPasswordFormType } from 'types/form';
import SimpleLayout from 'components/Layout/SimpleLayout';
import TextInput from 'components/Forms/TextInput';
import Trans from 'next-translate/Trans';
import { useAuth } from 'hooks/auth/UseAuth';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useRecoverPasswordMutationApi } from 'graphql/generated';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type NewPasswordPageProps = {
    hash: string;
    email: string;
};

const NewPasswordPage: FC<NewPasswordPageProps> = (props) => {
    const t = useTypedTranslationFunction();
    const [newPasswordResult, newPassword] = useRecoverPasswordMutationApi();
    const { url } = useShopsysSelector((state) => state.domain);
    const [newPasswordUrl, resetPasswordUrl] = getInternationalizedStaticUrls(
        ['/new-password', '/reset-password'],
        url,
    );
    const [formProviderMethods, defaultValues] = useRecoveryPasswordForm();
    const formMeta = useRecoveryPasswordFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    const [[, login]] = useAuth();
    const router = useRouter();
    const cartUuid = useShopsysSelector((state) => state.user.cartUuid);

    useHandleFormErrors(newPasswordResult.error, formProviderMethods, formMeta.messages.error, formMeta.fields);
    useHandleFormSuccessfulSubmit(
        newPasswordResult,
        formProviderMethods,
        defaultValues,
        () => {
            showSuccessMessage(formMeta.messages.success);
            if (newPasswordResult.data?.RecoverPassword.accessToken !== undefined) {
                login({
                    email: props.email,
                    password: formProviderMethods.getValues('newPassword'),
                    previousCartUuid: cartUuid,
                });
                router.push('/');
            }
        },
        { blur: true, reset: true },
    );

    const onNewPasswordHandler: SubmitHandler<NewPasswordFormType> = async (data, event) => {
        event?.preventDefault();
        const formData = {
            hash: props.hash,
            email: props.email,
            newPassword: data.newPassword,
        };
        await newPassword(formData);
    };

    useEffectOnce(() => {
        if (props.hash === '' || props.email === '') {
            showErrorMessage(t('Error occured while loading form data'));
        }
    });

    if (props.hash === '' || props.email === '') {
        return (
            <SimpleLayout
                heading={t('Set new password')}
                breadcrumb={[{ name: t('Set new password'), slug: newPasswordUrl }]}
            >
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
                breadcrumb={[{ name: t('Set new password'), slug: newPasswordUrl }]}
            >
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onNewPasswordHandler)} noValidate>
                        <Controller
                            name={formMeta.fields.newPassword.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <FormLine bottomGap={true}>
                                    <TextInput
                                        id={formMeta.formName + '-' + formMeta.fields.newPassword.name}
                                        name={formMeta.fields.newPassword.name}
                                        label={formMeta.fields.newPassword.label}
                                        required={true}
                                        type="password"
                                        isTouched={isTouched}
                                        hasError={invalid}
                                        fieldRef={field}
                                    />
                                    <FormLineError
                                        textInputSize="small"
                                        error={error}
                                        inputType="text-input-password"
                                        data-testid={
                                            formMeta.formName + '-' + formMeta.fields.newPassword.name + '-error'
                                        }
                                    />
                                </FormLine>
                            )}
                        />
                        <Controller
                            name={formMeta.fields.newPasswordAgain.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <FormLine>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.newPasswordAgain.name}
                                            name={formMeta.fields.newPasswordAgain.name}
                                            label={formMeta.fields.newPasswordAgain.label}
                                            required={true}
                                            type="password"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                        />
                                        <FormLineError
                                            textInputSize="small"
                                            error={error}
                                            inputType="text-input-password"
                                            data-testid={
                                                formMeta.formName +
                                                '-' +
                                                formMeta.fields.newPasswordAgain.name +
                                                '-error'
                                            }
                                        />
                                    </FormLine>
                                    <ButtonWrapperStyled>
                                        <Button type="submit" hasDisabledLook={invalid || field.value.length === 0}>
                                            {t('Set new password')}
                                        </Button>
                                    </ButtonWrapperStyled>
                                </>
                            )}
                        />
                    </Form>
                </FormProvider>
            </SimpleLayout>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
            />
        </>
    );
};

export default NewPasswordPage;
