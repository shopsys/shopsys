import { usePasswordResetForm, usePasswordResetFormMeta } from './passwordResetFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { usePasswordRecoveryMutation } from 'graphql/requests/passwordRecovery/mutations/PasswordRecoveryMutation.generated';
import { onGtmSendFormEventHandler } from 'gtm/helpers/eventHandlers';
import { GtmFormType, GtmMessageOriginType } from 'gtm/types/enums';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import 'helpers/getInternationalizedStaticUrls';
import { showSuccessMessage } from 'helpers/toasts';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler, useController } from 'react-hook-form';
import { PasswordResetFormType } from 'types/form';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

export const ResetPasswordContent: FC = () => {
    const { t } = useTranslation();
    const [, resetPassword] = usePasswordRecoveryMutation();
    const [formProviderMethods, defaultValues] = usePasswordResetForm();
    const formMeta = usePasswordResetFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);

    const {
        fieldState: { invalid },
        field: { value },
    } = useController({ name: formMeta.fields.email.name, control: formProviderMethods.control });

    const onResetPasswordHandler = useCallback<SubmitHandler<PasswordResetFormType>>(
        async (data) => {
            blurInput();
            const resetPasswordResult = await resetPassword(data);

            if (resetPasswordResult.data?.RequestPasswordRecovery !== undefined) {
                showSuccessMessage(formMeta.messages.success);
                onGtmSendFormEventHandler(GtmFormType.forgotten_password);
            }

            handleFormErrors(resetPasswordResult.error, formProviderMethods, t, formMeta.messages.error);
            clearForm(resetPasswordResult.error, formProviderMethods, defaultValues);
        },
        [formMeta.messages, formProviderMethods, resetPassword, t, defaultValues],
    );

    return (
        <>
            <SimpleLayout heading={t('Forgotten password')}>
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onResetPasswordHandler)}>
                        <TextInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.email.name}
                            render={(textInput) => <FormLine>{textInput}</FormLine>}
                            textInputProps={{
                                label: formMeta.fields.email.label,
                                required: true,
                                type: 'email',
                                autoComplete: 'email',
                            }}
                        />
                        <div className="mt-8 flex w-full justify-center">
                            <SubmitButton isWithDisabledLook={invalid || value.length === 0}>
                                {t('Reset password')}
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
