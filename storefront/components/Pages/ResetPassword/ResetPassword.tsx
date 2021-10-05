import * as Yup from 'yup';
import Button from 'components/Forms/Button';
import { ButtonWrapperStyled } from './ResetPassword.style';
import { Controller } from 'react-hook-form';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import SimpleLayout from 'components/Layout/SimpleLayout';
import TextInput from 'components/Forms/TextInput';
import { TFunction } from 'react-i18next';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { usePasswordReset } from 'connectors/password/Password';
import { useShopsysSelector } from 'redux/store';
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
    const [, resetPassword] = usePasswordReset();
    const { url } = useShopsysSelector((state) => state.domain);
    const [resetPasswordUrl] = useGetInternationalizedStaticUrls(['/reset-password'], url);

    return (
        <SimpleLayout
            heading={t('Forgotten password')}
            breadcrumb={[{ __typename: 'Link', name: t('Forgotten password'), slug: resetPasswordUrl }]}
        >
            <Form
                defaultValues={{ email: '' }}
                onSubmitHandler={resetPassword}
                onSuccessHandler={() => showSuccessMessage(t('We sent an email with further steps to your address'))}
                resolver={getResetPasswordFormResolver(t)}
            >
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
            </Form>
        </SimpleLayout>
    );
};

export default ResetPassword;
