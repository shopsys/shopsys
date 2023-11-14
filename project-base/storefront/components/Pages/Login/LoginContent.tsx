import { yupResolver } from '@hookform/resolvers/yup';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useAuth } from 'hooks/auth/useAuth';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useGetAllProductListUuids } from 'hooks/useGetAllProductListUuids';
import { Translate } from 'next-translate';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { FormProvider } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import * as Yup from 'yup';

const TEST_IDENTIFIER = 'pages-login-submit';

export const LoginContent: FC = () => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { url } = useDomainConfig();
    const router = useRouter();
    const formProviderMethods = useShopsysForm(getLoginFormResolver(t), { email: '', password: '' });
    const { login } = useAuth();
    const getProductListUuids = useGetAllProductListUuids();

    const onLoginHandler = async (data: { email: string; password: string }) => {
        let redirectUrl = url;

        if (typeof router.query.r === 'string') {
            redirectUrl = router.query.r;
        }

        const loginResult = await login(
            {
                email: data.email,
                password: data.password,
                previousCartUuid: cartUuid,
                productListsUuids: getProductListUuids(),
            },
            redirectUrl,
        );

        handleFormErrors(loginResult.error, formProviderMethods, t);
    };

    return (
        <SimpleLayout heading={t('Login')}>
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onLoginHandler)}>
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
                    <div className="mt-8 flex w-full justify-center">
                        <SubmitButton dataTestId={TEST_IDENTIFIER}>{t('Log in')}</SubmitButton>
                    </div>
                </Form>
            </FormProvider>
        </SimpleLayout>
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
