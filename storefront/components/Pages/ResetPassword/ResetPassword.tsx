import * as Yup from 'yup';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import Button from 'components/Forms/Button';
import { ButtonWrapperStyled } from './ResetPassword.style';
import { FC } from 'react';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import SimpleLayout from 'components/Layout/SimpleLayout';
import TextInput from 'components/Forms/TextInput';
import { TFunction } from 'react-i18next';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useHandleFormValidationErrors } from 'hooks/forms/UseHandleFormValidationErrors';
import { usePasswordReset } from 'connectors/password/Password';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

const getResetPasswordFormResolver = (t: TFunction) => {
    return yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
        }),
    );
};

const ResetPassword: FC = () => {
    const t = useTypedTranslationFunction();
    const [resetPasswordResult, resetPassword] = usePasswordReset();
    const { url } = useShopsysSelector((state) => state.domain);
    const [resetPasswordUrl] = useGetInternationalizedStaticUrls(['/reset-password'], url);
    const formProviderMethods = useShopsysForm(getResetPasswordFormResolver(t), { email: '' });
    useHandleFormValidationErrors(resetPasswordResult.error, formProviderMethods);
    useHandleFormSuccessfulSubmit(
        resetPasswordResult,
        formProviderMethods,
        { email: '' },
        () => showSuccessMessage(t('We sent an email with further steps to your address')),
        { blur: true, reset: true },
    );

    const onResetPasswordHandler: SubmitHandler<{ email: string }> = (data, event) => {
        event?.preventDefault();
        resetPassword(data);
    };

    return (
        <SimpleLayout
            heading={t('Forgotten password')}
            breadcrumb={[{ __typename: 'Link', name: t('Forgotten password'), slug: resetPasswordUrl }]}
        >
            <FormProvider {...formProviderMethods}>
                <form onSubmit={formProviderMethods.handleSubmit(onResetPasswordHandler)}>
                    <Controller
                        name="email"
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <FormLine>
                                    <TextInput
                                        id="reset-password_form-email"
                                        name="email"
                                        label={t('Your email')}
                                        required={true}
                                        type="text"
                                        isTouched={isTouched}
                                        hasError={invalid}
                                        fieldRef={field}
                                    />
                                    <FormLineError textInputSize="small" error={error} inputType="text-input" />
                                </FormLine>
                                <ButtonWrapperStyled>
                                    <Button type="submit" isDisabled={invalid || field.value.length === 0}>
                                        {t('Reset password')}
                                    </Button>
                                </ButtonWrapperStyled>
                            </>
                        )}
                    />
                </form>
            </FormProvider>
        </SimpleLayout>
    );
};

export default ResetPassword;
