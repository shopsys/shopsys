import * as Yup from 'yup';
import {
    ButtonsStyled,
    ButtonWrapperStyled,
    LoginColumnStyled,
    LoginLostPassIconStyled,
    LoginLostPassLinkMobileStyled,
    LoginLostPassLinkStyled,
    LoginLostPassStyled,
    LoginLostPassTextStyled,
    LoginMessageStyled,
    LoginProfileIconStyled,
    LoginProfileStyled,
    LoginProfileTextStyled,
    LoginStyled,
} from './Login.style';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import Button from 'components/Forms/Button';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Image from 'next/image';
import Link from 'components/Basic/Link';
import NextLink from 'next/link';
import TextInput from 'components/Forms/TextInput';
import { TFunction } from 'react-i18next';
import { useAuth } from 'hooks/auth/UseAuth';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
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
    const testIdentifier = 'blocks-popup-login';

    const t = useTypedTranslationFunction();
    const { cartUuid } = useShopsysSelector((state) => state.cart.cartInput);
    const { url } = useShopsysSelector((state) => state.domain);
    const [resetPasswordUrl, registrationUrl] = useGetInternationalizedStaticUrls(
        ['/reset-password', '/registration'],
        url,
    );
    const formProviderMethods = useShopsysForm(getLoginFormResolver(t), { email: '', password: '' });
    const [[loginResult, login]] = useAuth();

    useHandleFormSuccessfulSubmit(loginResult, formProviderMethods, { email: '', password: '' }, undefined, {
        blur: true,
    });

    const onLoginHandler: SubmitHandler<{ email: string; password: string }> = (data, event) => {
        event?.preventDefault();
        login({ email: data.email, password: data.password, previousCartUuid: cartUuid });
    };

    return (
        <LoginStyled data-testid={testIdentifier}>
            <LoginColumnStyled>
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
                        <ButtonsStyled>
                            <ButtonWrapperStyled>
                                <Button type="submit">{t('Log-in')}</Button>
                            </ButtonWrapperStyled>
                            <LoginLostPassStyled>
                                <LoginLostPassIconStyled iconType="icon" icon="Warning" />
                                <LoginLostPassTextStyled>{t('Lost your password?')}</LoginLostPassTextStyled>
                                <NextLink href={resetPasswordUrl} passHref>
                                    <LoginLostPassLinkStyled>{t('Renew it')}</LoginLostPassLinkStyled>
                                </NextLink>
                                <NextLink href={resetPasswordUrl} passHref>
                                    <LoginLostPassLinkMobileStyled>
                                        {t('Lost your password?')}
                                    </LoginLostPassLinkMobileStyled>
                                </NextLink>
                            </LoginLostPassStyled>
                        </ButtonsStyled>
                    </Form>
                </FormProvider>
            </LoginColumnStyled>
            <LoginColumnStyled>
                <LoginProfileStyled>
                    <LoginProfileTextStyled>{t("Don't have an account yet? Register.")}</LoginProfileTextStyled>
                    <LoginProfileIconStyled>
                        <Image
                            src="/images/qmark.png"
                            height={120}
                            width={75}
                            alt={t("Don't have an account yet? Register.")}
                        />
                    </LoginProfileIconStyled>
                </LoginProfileStyled>
                <LoginMessageStyled>
                    {t('Your addresses prefilled and you can check your order history.')}
                </LoginMessageStyled>
                <Link isButton={true} href={registrationUrl}>
                    {t('Register')}
                </Link>
            </LoginColumnStyled>
        </LoginStyled>
    );
};

export default Login;
