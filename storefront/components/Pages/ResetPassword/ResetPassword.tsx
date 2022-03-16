import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { usePasswordResetForm, usePasswordResetFormMeta } from './formMeta';
import Button from 'components/Forms/Button';
import { ButtonWrapperStyled } from './ResetPassword.style';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { PasswordResetFormType } from 'types/form';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import SimpleLayout from 'components/Layout/SimpleLayout';
import TextInput from 'components/Forms/TextInput';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { usePasswordRecoveryMutationApi } from 'graphql/generated';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ResetPassword: FC = () => {
    const t = useTypedTranslationFunction();
    const [resetPasswordResult, resetPassword] = usePasswordRecoveryMutationApi();
    const { url } = useShopsysSelector((state) => state.domain);
    const [resetPasswordUrl] = getInternationalizedStaticUrls(['/reset-password'], url);
    const [formProviderMethods, defaultValues] = usePasswordResetForm();
    const formMeta = usePasswordResetFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    useHandleFormErrors(resetPasswordResult.error, formProviderMethods, formMeta.messages.error);
    useHandleFormSuccessfulSubmit(
        resetPasswordResult,
        formProviderMethods,
        defaultValues,
        () => showSuccessMessage(formMeta.messages.success),
        { blur: true, reset: true },
    );

    const onResetPasswordHandler: SubmitHandler<PasswordResetFormType> = async (data, event) => {
        event?.preventDefault();
        await resetPassword(data);
    };

    return (
        <>
            <SimpleLayout
                heading={t('Forgotten password')}
                breadcrumb={[{ name: t('Forgotten password'), slug: resetPasswordUrl }]}
            >
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onResetPasswordHandler)} noValidate>
                        <Controller
                            name={formMeta.fields.email.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <FormLine>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.email.name}
                                            name={formMeta.fields.email.name}
                                            label={formMeta.fields.email.label}
                                            required={true}
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                        />
                                        <FormLineError
                                            textInputSize="small"
                                            error={error}
                                            inputType="text-input"
                                            data-testid={
                                                formMeta.formName + '-' + formMeta.fields.email.name + '-error'
                                            }
                                        />
                                    </FormLine>
                                    <ButtonWrapperStyled>
                                        <Button type="submit" hasDisabledLook={invalid || field.value.length === 0}>
                                            {t('Reset password')}
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

export default ResetPassword;
