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
import { useHandleErrorPopupVisibility } from 'hooks/forms/useHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/useHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/useHandleFormSuccessfulSubmit';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { FormProvider, SubmitHandler, useController } from 'react-hook-form';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { PasswordResetFormType } from 'types/form';

type ResetPasswordContentProps = {
    breadcrumbs: BreadcrumbItemType[];
};

export const ResetPasswordContent: FC<ResetPasswordContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const [resetPasswordResult, resetPassword] = usePasswordRecoveryMutationApi();
    const [formProviderMethods, defaultValues] = usePasswordResetForm();
    const formMeta = usePasswordResetFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    useHandleFormErrors(resetPasswordResult.error, formProviderMethods, 'other', formMeta.messages.error);
    useHandleFormSuccessfulSubmit(
        resetPasswordResult,
        formProviderMethods,
        defaultValues,
        () => showSuccessMessage(formMeta.messages.success),
        { blur: true, reset: true },
    );

    const {
        fieldState: { invalid },
        field: { value },
    } = useController({ name: formMeta.fields.email.name, control: formProviderMethods.control });

    const onResetPasswordHandler: SubmitHandler<PasswordResetFormType> = async (data, event) => {
        event?.preventDefault();
        await resetPassword(data);
    };

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
