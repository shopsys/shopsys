'use client';

import { resetPasswordAction } from 'app/_actions/resetPasswordAction';
import {
    useResetPasswordForm,
    useResetPasswordFormMeta,
} from 'app/_components/ResetPasswordForm/resetPasswordFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TypePasswordRecoveryMutationVariables } from 'graphql/requests/passwordRecovery/mutations/PasswordRecoveryMutation.ssr';
import { GtmFormType } from 'gtm/enums/GtmFormType';
import { onGtmSendFormEventHandler } from 'gtm/handlers/onGtmSendFormEventHandler';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { ResetPasswordFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export type ResetPasswordFormProps = {
    formHeading: string;
};

export const ResetPasswordForm: FC<ResetPasswordFormProps> = ({ formHeading }) => {
    const { t } = useTranslation();
    const [formProviderMethods, defaultValues] = useResetPasswordForm();
    const formMeta = useResetPasswordFormMeta(formProviderMethods);

    const onResetPasswordHandler: SubmitHandler<ResetPasswordFormType> = async (formData) => {
        blurInput();

        const resetPasswordData: TypePasswordRecoveryMutationVariables = {
            email: formData.email,
        };

        const response = await resetPasswordAction(resetPasswordData);

        if (response.error) {
            handleFormErrors(response.error, formProviderMethods, t, formMeta.messages.error);

            return;
        }

        showSuccessMessage(formMeta.messages.success);

        onGtmSendFormEventHandler(GtmFormType.forgotten_password);

        clearForm(response.error, formProviderMethods, defaultValues);
    };

    return (
        <FormProvider {...formProviderMethods}>
            <Form
                className="flex w-full justify-center"
                onSubmit={formProviderMethods.handleSubmit(onResetPasswordHandler)}
            >
                <FormContentWrapper>
                    <FormBlockWrapper>
                        <FormHeading>{formHeading}</FormHeading>

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
                        <FormButtonWrapper>
                            <SubmitButton>{t('Reset password')}</SubmitButton>
                        </FormButtonWrapper>
                    </FormBlockWrapper>
                </FormContentWrapper>
            </Form>
        </FormProvider>
    );
};
