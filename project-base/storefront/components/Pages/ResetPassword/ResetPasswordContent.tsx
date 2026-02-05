import { usePasswordResetForm, usePasswordResetFormMeta } from './passwordResetFormMeta';
import { LockIcon } from 'components/Basic/Icon/LockIcon';
import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { usePasswordRecoveryMutation } from 'graphql/requests/passwordRecovery/mutations/PasswordRecoveryMutation.generated';
import { GtmFormType } from 'gtm/enums/GtmFormType';
import { onGtmSendFormEventHandler } from 'gtm/handlers/onGtmSendFormEventHandler';
import { useState } from 'react';
import { FormProvider, SubmitHandler, useController } from 'react-hook-form';
import { PasswordResetFormType } from 'types/form';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ResetPasswordContent: FC = () => {
    const { t } = useTranslation();
    const [, resetPassword] = usePasswordRecoveryMutation();
    const [formProviderMethods, defaultValues] = usePasswordResetForm();
    const formMeta = usePasswordResetFormMeta(formProviderMethods);
    const [isSuccess, setIsSuccess] = useState(false);
    const handleError = useErrorHandler({
        form: formProviderMethods,
        customMessage: formMeta.messages.error,
    });

    const {
        fieldState: { error },
        field: { value },
    } = useController({ name: formMeta.fields.email.name, control: formProviderMethods.control });

    const onResetPasswordHandler: SubmitHandler<PasswordResetFormType> = async (passwordResetFormData) => {
        blurInput();
        const resetPasswordResult = await resetPassword(passwordResetFormData);

        if (resetPasswordResult.data?.RequestPasswordRecovery) {
            setIsSuccess(true);
            onGtmSendFormEventHandler(GtmFormType.forgotten_password);
        }

        handleError(resetPasswordResult.error);

        clearForm(resetPasswordResult.error, formProviderMethods, defaultValues);
    };

    return (
        <Webline width="lg">
            <VerticalStack gap="sm">
                {isSuccess && (
                    <PageHero description={formMeta.messages.success} icon={MailIcon} title={t('Check your email')} />
                )}

                {!isSuccess && (
                    <>
                        <PageHero
                            icon={LockIcon}
                            title={t('Reset your password')}
                            description={t(
                                'Forgot your password? Enter your email address below and we will send you a link to create a new password.',
                            )}
                        />

                        <FormProvider {...formProviderMethods}>
                            <Form
                                className="flex w-full justify-center"
                                onSubmit={formProviderMethods.handleSubmit(onResetPasswordHandler)}
                            >
                                <FormContentWrapper>
                                    <FormBlockWrapper>
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
                                    </FormBlockWrapper>

                                    <FormButtonWrapper>
                                        <SubmitButton
                                            hasDisabledCursor={!!error || value.length === 0}
                                            aria-label={t('Submit form to reset your password', {
                                                ns: 'accessibility',
                                            })}
                                        >
                                            {t('Reset password')}
                                        </SubmitButton>
                                    </FormButtonWrapper>
                                </FormContentWrapper>
                            </Form>
                        </FormProvider>
                    </>
                )}
            </VerticalStack>
        </Webline>
    );
};
