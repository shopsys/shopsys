import * as Yup from 'yup';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import Button from 'components/Forms/Button';
import { ButtonWrapperStyled } from './Login.style';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import SimpleLayout from 'components/Layout/SimpleLayout';
import TextInput from 'components/Forms/TextInput';
import { TFunction } from 'react-i18next';
import { useAuth } from 'hooks/auth/UseAuth';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useRouter } from 'next/router';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

const getLoginFormResolver = (t: TFunction) => {
    return yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
            password: Yup.string().required(t('This field is required')),
        }),
    );
};

const Login: FC = () => {
    const t = useTypedTranslationFunction();
    const { cartUuid } = useShopsysSelector((state) => state.cart.cartInput);
    const { url } = useShopsysSelector((state) => state.domain);
    const [loginUrl] = useGetInternationalizedStaticUrls(['/login'], url);
    const formProviderMethods = useShopsysForm(getLoginFormResolver(t), { email: '', password: '' });
    const [[loginResult, login]] = useAuth();
    const router = useRouter();

    useHandleFormErrors(undefined, formProviderMethods);
    useHandleFormSuccessfulSubmit(
        loginResult,
        formProviderMethods,
        { email: '', password: '' },
        () => router.push('/'),
        { blur: true, reset: true },
    );

    const onLoginHandler: SubmitHandler<{ email: string; password: string }> = async (data, event) => {
        event?.preventDefault();
        await login({ email: data.email, password: data.password, previousCartUuid: cartUuid });
    };

    return (
        <SimpleLayout heading={t('Login')} breadcrumb={[{ name: t('Login'), slug: loginUrl }]}>
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onLoginHandler)}>
                    <Controller
                        name="email"
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <FormLine bottomGap={true}>
                                    <TextInput
                                        id="login_form-email"
                                        name="email"
                                        label={t('Your email')}
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
                                        data-testid="login_form-email-error"
                                    />
                                </FormLine>
                            </>
                        )}
                    />
                    <Controller
                        name="password"
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <FormLine>
                                    <TextInput
                                        id="login_form-password"
                                        name="password"
                                        label={t('Password')}
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
                                        data-testid="login_form-password-error"
                                    />
                                </FormLine>
                            </>
                        )}
                    />

                    <ButtonWrapperStyled>
                        <Button type="submit">{t('Log in')}</Button>
                    </ButtonWrapperStyled>
                </Form>
            </FormProvider>
        </SimpleLayout>
    );
};

export default Login;
