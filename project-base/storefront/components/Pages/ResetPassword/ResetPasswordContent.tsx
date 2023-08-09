import { usePasswordResetForm, usePasswordResetFormMeta } from './passwordResetFormMeta';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { showSuccessMessage } from 'components/Helpers/toasts';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { BreadcrumbFragmentApi, usePasswordRecoveryMutationApi } from 'graphql/generated';
import 'helpers//localization/getInternationalizedStaticUrls';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { onGtmSendFormEventHandler } from 'helpers/gtm/eventHandlers';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import dynamic from 'next/dynamic';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler, useController } from 'react-hook-form';
import { PasswordResetFormType } from 'types/form';
import { GtmFormType, GtmMessageOriginType } from 'types/gtm/enums';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

type ResetPasswordContentProps = {
    breadcrumbs: BreadcrumbFragmentApi[];
};

export const ResetPasswordContent: FC<ResetPasswordContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const [, resetPassword] = usePasswordRecoveryMutationApi();
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
            <SimpleLayout heading={t('Forgotten password')} breadcrumb={breadcrumbs}>
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onResetPasswordHandler)}>
                        <TextInputControlled
                            control={formProviderMethods.control}
                            name={formMeta.fields.email.name}
                            render={(textInput) => <FormLine>{textInput}</FormLine>}
                            formName={formMeta.formName}
                            textInputProps={{
                                label: formMeta.fields.email.label,
                                required: true,
                                type: 'email',
                                autoComplete: 'email',
                            }}
                        />
                        <div className="mt-8 flex w-full justify-center">
                            <Button type="submit" isWithDisabledLook={invalid || value.length === 0}>
                                {t('Reset password')}
                            </Button>
                        </div>
                    </Form>
                </FormProvider>
            </SimpleLayout>
            {isErrorPopupVisible && (
                <ErrorPopup
                    onCloseCallback={() => setErrorPopupVisibility(false)}
                    fields={formMeta.fields}
                    gtmMessageOrigin={GtmMessageOriginType.other}
                />
            )}
        </>
    );
};
