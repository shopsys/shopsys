import { ButtonWrapperStyled } from './LoginContent.style';
import { yupResolver } from '@hookform/resolvers/yup';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useAuth } from 'hooks/auth/useAuth';
import { useHandleFormErrors } from 'hooks/forms/useHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/useHandleFormSuccessfulSubmit';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { Translate } from 'next-translate';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { BreadcrumbItemType } from 'types/breadcrumb';
import * as Yup from 'yup';

type LoginContentProps = {
    breadcrumbs: BreadcrumbItemType[];
};

const TEST_IDENTIFIER = 'pages-login-submit';

export const LoginContent: FC<LoginContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { url } = useShopsysSelector((state) => state.domain);
    const router = useRouter();
    const formProviderMethods = useShopsysForm(getLoginFormResolver(t), { email: '', password: '' });
    const [[loginResult, login]] = useAuth();

    useHandleFormErrors(loginResult.error, formProviderMethods, 'other');
    useHandleFormSuccessfulSubmit(loginResult, formProviderMethods, { email: '', password: '' });

    const onLoginHandler: SubmitHandler<{ email: string; password: string }> = async (data, event) => {
        event?.preventDefault();

        let redirectUrl = url;

        if (typeof router.query.r === 'string') {
            redirectUrl = router.query.r;
        }

        await login({ email: data.email, password: data.password, previousCartUuid: cartUuid }, redirectUrl);
    };

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
                    <ButtonWrapperStyled>
                        <Button type="submit" testIdentifier={TEST_IDENTIFIER}>
                            {t('Log in')}
                        </Button>
                    </ButtonWrapperStyled>
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
