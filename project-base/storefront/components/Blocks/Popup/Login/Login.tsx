import { yupResolver } from '@hookform/resolvers/yup';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Icon } from 'components/Basic/Icon/Icon';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { blurInput } from 'helpers/forms/blurInput';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { Translate } from 'next-translate';
import Image from 'next/image';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { GtmMessageOriginType } from 'types/gtm/enums';
import * as Yup from 'yup';

type LoginProps = {
    defaultEmail?: string;
};

const TEST_IDENTIFIER = 'blocks-popup-login';

export const Login: FC<LoginProps> = ({ defaultEmail }) => {
    const t = useTypedTranslationFunction();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { url } = useDomainConfig();
    const [resetPasswordUrl, registrationUrl] = getInternationalizedStaticUrls(
        ['/reset-password', '/registration'],
        url,
    );
    const formProviderMethods = useShopsysForm(getLoginFormResolver(t), { email: defaultEmail ?? '', password: '' });
    const { login } = useAuth();

    const onLoginHandler = useCallback<SubmitHandler<{ email: string; password: string }>>(
        async (data) => {
            blurInput();
            const loginResponse = await login({
                email: data.email,
                password: data.password,
                previousCartUuid: cartUuid,
            });

            handleFormErrors(
                loginResponse.error,
                formProviderMethods,
                t,
                undefined,
                undefined,
                GtmMessageOriginType.login_popup,
            );
        },
        [login, cartUuid, formProviderMethods, t],
    );

    return (
        <div
            className="flex w-full max-w-xs flex-col items-center sm:max-w-md md:max-w-2xl lg:max-w-3xl lg:flex-row"
            data-testid={TEST_IDENTIFIER}
        >
            <div className="w-full border-b border-primary lg:w-1/2 lg:border-b-0 lg:border-r lg:pr-5">
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
                        <div className="mt-5 mb-5 flex items-center justify-between gap-2 lg:mb-0 lg:block lg:border-none lg:p-0">
                            <div className="order-1 flex w-full justify-end">
                                <Button type="submit" dataTestId="blocks-popup-login-submit" className="max-lg:!px-3">
                                    {t('Log-in')}
                                </Button>
                            </div>
                            <div className="flex items-center gap-1 whitespace-nowrap rounded-xl border-primary py-2 px-2 text-sm text-primary lg:mt-5 lg:border-2 lg:px-3 lg:py-3">
                                <Icon iconType="icon" icon="Warning" className="hidden h-5 w-9 text-red sm:block" />
                                <div className="hidden flex-grow lg:block">{t('Lost your password?')}</div>
                                <ExtendedNextLink href={resetPasswordUrl} type="static">
                                    <div className="hidden cursor-pointer text-primary underline hover:no-underline lg:block">
                                        {t('Renew it')}
                                    </div>
                                </ExtendedNextLink>
                                <ExtendedNextLink href={resetPasswordUrl} type="static">
                                    <div className="block text-sm text-primary underline hover:no-underline lg:hidden">
                                        {t('Lost your password?')}
                                    </div>
                                </ExtendedNextLink>
                            </div>
                        </div>
                    </Form>
                </FormProvider>
            </div>
            <div className="mt-7 w-full lg:mt-0 lg:w-1/2 lg:pl-5">
                <div className="relative mb-6 -mr-4 w-full rounded-l-xl bg-blueLight p-4">
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
