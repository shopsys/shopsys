import { ButtonWrapperStyled } from './LoginContent.style';
import { yupResolver } from '@hookform/resolvers/yup';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useAuth } from 'hooks/auth/UseAuth';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { Translate } from 'next-translate';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { BreadcrumbItemType } from 'types/breadcrumb';
import * as Yup from 'yup';

const getLoginFormResolver = (t: Translate) => {
    return yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
            password: Yup.string().required(t('This field is required')),
        }),
    );
};

type LoginContentProps = {
    breadcrumbs: BreadcrumbItemType[];
};

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

    const testIdentifier = 'pages-login-submit';

    return (
        <SimpleLayout heading={t('Login')} breadcrumb={breadcrumbs}>
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
                        <Button type="submit" data-testid={testIdentifier}>
                            {t('Log in')}
                        </Button>
                    </ButtonWrapperStyled>
                </Form>
            </FormProvider>
        </SimpleLayout>
    );
};
