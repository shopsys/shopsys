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
import { yupResolver } from '@hookform/resolvers/yup';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { blurInput } from 'helpers/forms/blurInput';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { Translate } from 'next-translate';
import Image from 'next/image';
import NextLink from 'next/link';
import { FC, useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import * as Yup from 'yup';

const TEST_IDENTIFIER = 'blocks-popup-login';

export const Login: FC = () => {
    const t = useTypedTranslationFunction();
    const cartUuid = useShopsysSelector((state) => state.user.cartUuid);
    const { url } = useShopsysSelector((state) => state.domain);
    const [resetPasswordUrl, registrationUrl] = getInternationalizedStaticUrls(
        ['/reset-password', '/registration'],
        url,
    );
    const formProviderMethods = useShopsysForm(getLoginFormResolver(t), { email: '', password: '' });
    const { login } = useAuth();

    const onLoginHandler = useCallback<SubmitHandler<{ email: string; password: string }>>(
        async (data) => {
            blurInput();
            const loginResponse = await login({
                email: data.email,
                password: data.password,
                previousCartUuid: cartUuid,
            });

            handleFormErrors(loginResponse.error, formProviderMethods, 'login popup', t);
        },
        [login, cartUuid, formProviderMethods, t],
    );

    return (
        <LoginStyled data-testid={TEST_IDENTIFIER}>
            <LoginColumnStyled>
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onLoginHandler)}>
                        <TextInputControlled
                            control={formProviderMethods.control}
                            name="email"
                            render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                            formName="login-form"
                            textInputProps={{
                                label: t('Your email'),
                                required: true,
                                type: 'text',
                            }}
                        />
                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            name="password"
                            render={(passwordInput) => <FormLine>{passwordInput}</FormLine>}
                            formName="login-form"
                            passwordInputProps={{
                                label: t('Password'),
                            }}
                        />
                        <ButtonsStyled>
                            <ButtonWrapperStyled>
                                <Button type="submit" data-testid="blocks-popup-login-submit">
                                    {t('Log-in')}
                                </Button>
                            </ButtonWrapperStyled>
                            <LoginLostPassStyled>
                                <LoginLostPassIconStyled alt="" iconType="icon" icon="Warning" />
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
                <Link isButton href={registrationUrl}>
                    {t('Register')}
                </Link>
            </LoginColumnStyled>
        </LoginStyled>
    );
};

const getLoginFormResolver = (t: Translate) => {
    return yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
            password: Yup.string().required(t('This field is required')),
        }),
    );
};
