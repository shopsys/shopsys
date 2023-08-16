import { yupResolver } from '@hookform/resolvers/yup';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useAuth } from 'hooks/auth/useAuth';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { Translate } from 'next-translate';
import { useRouter } from 'next/router';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import * as Yup from 'yup';

type LoginContentProps = {
    breadcrumbs: BreadcrumbFragmentApi[];
};

const TEST_IDENTIFIER = 'pages-login-submit';

export const LoginContent: FC<LoginContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { url } = useDomainConfig();
    const router = useRouter();
    const formProviderMethods = useShopsysForm(getLoginFormResolver(t), { email: '', password: '' });
    const { login } = useAuth();

    const onLoginHandler = useCallback<SubmitHandler<{ email: string; password: string }>>(
        async (data) => {
            let redirectUrl = url;

            if (typeof router.query.r === 'string') {
                redirectUrl = router.query.r;
            }

            const loginResult = await login(
                { email: data.email, password: data.password, previousCartUuid: cartUuid },
                redirectUrl,
            );

            handleFormErrors(loginResult.error, formProviderMethods, t);
        },
        [cartUuid, formProviderMethods, login, router.query.r, t, url],
    );

    return (
        <SimpleLayout heading={t('Login')} breadcrumb={breadcrumbs}>
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
                    <div className="mt-8 flex w-full justify-center">
                        <Button type="submit" dataTestId={TEST_IDENTIFIER}>
                            {t('Log in')}
                        </Button>
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
