import { yupResolver } from '@hookform/resolvers/yup';
import { Icon } from 'components/Basic/Icon/Icon';
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
import { useCallback } from 'react';
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
        <div
            className='relative flex w-full max-w-xs flex-col items-start before:absolute  before:left-1/2 before:hidden before:h-full before:w-[1px] before:bg-primary before:content-[""] sm:max-w-md md:max-w-2xl lg:max-w-3xl lg:flex-row lg:before:block'
            data-testid={TEST_IDENTIFIER}
        >
            <div className="w-full pr-5 lg:w-1/2">
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
                                type: 'email',
                                autoComplete: 'email',
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
                        <div className="-mx-4 mb-10 flex items-center justify-between border-b border-primary px-4 pb-5 lg:m-0 lg:block lg:border-none lg:p-0">
                            <div className="order-1 mt-5 flex w-full justify-end">
                                <Button type="submit" data-testid="blocks-popup-login-submit" className="max-lg:!px-3">
                                    {t('Log-in')}
                                </Button>
                            </div>
                            <div className="mt-5 flex items-center whitespace-nowrap rounded-xl border-2 border-primary py-2 px-2 text-sm text-primary lg:px-3 lg:py-2">
                                <Icon
                                    iconType="icon"
                                    icon="Warning"
                                    width={29}
                                    height={29}
                                    className="mr-1 ml-1 hidden text-red sm:block"
                                />
                                <div className="hidden flex-grow lg:block">{t('Lost your password?')}</div>
                                <NextLink href={resetPasswordUrl} passHref>
                                    <div className="hidden cursor-pointer text-primary underline hover:no-underline lg:block">
                                        {t('Renew it')}
                                    </div>
                                </NextLink>
                                <NextLink href={resetPasswordUrl} passHref>
                                    <div className="block text-sm text-primary underline hover:no-underline lg:hidden">
                                        {t('Lost your password?')}
                                    </div>
                                </NextLink>
                            </div>
                        </div>
                    </Form>
                </FormProvider>
            </div>
            <div className="w-full pr-5 pl-5 lg:w-1/2">
                <div className="relative my-6 -mr-4 w-full rounded-l-xl bg-blueLight p-4">
                    <div className="block w-44 text-lg text-primary lg:w-72 lg:pr-24 lg:text-xl">
                        {t("Don't have an account yet? Register.")}
                    </div>
                    <div className="absolute right-0 bottom-0 h-24 overflow-hidden md:right-3 lg:h-28">
                        <Image
                            src="/images/qmark.png"
                            height={120}
                            width={75}
                            alt={t("Don't have an account yet? Register.")}
                            className="!max-w-none"
                        />
                    </div>
                </div>
                <p className="mb-8 hidden lg:block">
                    {t('Your addresses prefilled and you can check your order history.')}
                </p>
                <Link isButton href={registrationUrl}>
                    {t('Register')}
                </Link>
            </div>
        </div>
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
