import { yupResolver } from '@hookform/resolvers/yup';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { Translate } from 'next-translate';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { FormProvider } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { useLogin } from 'utils/auth/useLogin';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const LoginContent: FC = () => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { url } = useDomainConfig();
    const router = useRouter();
    const formProviderMethods = useShopsysForm(getLoginFormResolver(t), { email: '', password: '' });
    const login = useLogin();

    const onLoginHandler = async (loginFormData: { email: string; password: string }) => {
        let redirectUrl = url;

        if (typeof router.query.r === 'string') {
            redirectUrl = router.query.r;
        }

        const loginResult = await login(
            {
                email: loginFormData.email,
                password: loginFormData.password,
                previousCartUuid: cartUuid,
            },
            redirectUrl,
        );

        handleFormErrors(loginResult.error, formProviderMethods, t);
    };

    return (
        <Webline className="flex flex-col items-center">
            <h1 className="max-w-3xl w-full">{t('Login')}</h1>
            <FormProvider {...formProviderMethods}>
                <Form
                    className="w-full flex justify-center"
                    onSubmit={formProviderMethods.handleSubmit(onLoginHandler)}
                >
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName="login-form"
                                name="email"
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                textInputProps={{
                                    label: t('Your email'),
                                    required: true,
                                    type: 'email',
                                    autoComplete: 'email',
                                }}
                            />
                            <PasswordInputControlled
                                control={formProviderMethods.control}
                                formName="login-form"
                                name="password"
                                render={(passwordInput) => <FormLine>{passwordInput}</FormLine>}
                                passwordInputProps={{
                                    label: t('Password'),
                                }}
                            />
                            <FormButtonWrapper>
                                <SubmitButton tid={TIDs.pages_login_submit}>{t('Log in')}</SubmitButton>
                            </FormButtonWrapper>
                        </FormBlockWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Webline>
    );
};

const getLoginFormResolver = (t: Translate) => {
    return yupResolver(
        Yup.object().shape<Record<keyof { email: string; password: string }, any>>({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
            password: Yup.string().required(t('This field is required')),
        }),
    );
};
