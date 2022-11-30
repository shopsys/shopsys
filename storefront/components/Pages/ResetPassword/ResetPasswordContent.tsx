import { usePasswordResetForm, usePasswordResetFormMeta } from './formMeta';
import { ButtonWrapperStyled } from './ResetPasswordContent.style';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { usePasswordRecoveryMutationApi } from 'graphql/generated';
import 'helpers//localization/getInternationalizedStaticUrls';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useCallback } from 'react';
import { FormProvider, SubmitHandler, useController } from 'react-hook-form';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { PasswordResetFormType } from 'types/form';

type ResetPasswordContentProps = {
    breadcrumbs: BreadcrumbItemType[];
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
            }

            handleFormErrors(resetPasswordResult.error, formProviderMethods, 'other', t, formMeta.messages.error);
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
                                type: 'text',
                            }}
                        />
                        <ButtonWrapperStyled>
                            <Button type="submit" hasDisabledLook={invalid || value.length === 0}>
                                {t('Reset password')}
                            </Button>
                        </ButtonWrapperStyled>
                    </Form>
                </FormProvider>
            </SimpleLayout>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
                origin="other"
            />
        </>
    );
};
