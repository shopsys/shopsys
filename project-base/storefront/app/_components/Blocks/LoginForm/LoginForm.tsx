'use client';

import { useLoginForm, useLoginFormMeta } from 'app/_components/Blocks/LoginForm/loginFormMeta';
import { SocialNetworkLogin } from 'app/_components/Blocks/SocialNetworkLogin/SocialNetworkLogin';
import { useLogin } from 'app/_hooks/useLogin';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettings } from 'components/providers/SettingsProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider } from 'react-hook-form';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export type LoginFormProps = {
    defaultEmail?: string;
    shouldOverwriteCustomerUserCart?: boolean;
    formContentWrapperClassName?: string;
    formHeading: string;
};

export const LoginForm: FC<LoginFormProps> = ({
    defaultEmail,
    shouldOverwriteCustomerUserCart,
    formContentWrapperClassName,
    formHeading,
}) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [resetPasswordUrl] = getInternationalizedStaticUrls(['/reset-password'], url);

    const { socialNetworkLoginConfig } = useSettings();

    const [formProviderMethods] = useLoginForm(defaultEmail);
    const formMeta = useLoginFormMeta(formProviderMethods);

    const handleLogin = useLogin({ shouldOverwriteCustomerUserCart });

    return (
        <FormProvider {...formProviderMethods}>
            <Form className="flex w-full justify-center" onSubmit={formProviderMethods.handleSubmit(handleLogin)}>
                <FormContentWrapper className={formContentWrapperClassName}>
                    <FormBlockWrapper>
                        <FormHeading>{formHeading}</FormHeading>

                        <TextInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.email.name}
                            render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                            textInputProps={{
                                label: formMeta.fields.email.label,
                                required: true,
                                type: 'email',
                                autoComplete: 'email',
                            }}
                        />

                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.password.name}
                            render={(passwordInput) => <FormLine>{passwordInput}</FormLine>}
                            passwordInputProps={{
                                label: formMeta.fields.password.label,
                                autoComplete: 'current-password',
                            }}
                        />

                        <FormButtonWrapper className="mt-2.5 flex flex-col gap-4">
                            <FormButtonWrapper className="mt-0 justify-start">
                                <SubmitButton size="large" tid={TIDs.login_form_submit_button} variant="inverted">
                                    {t('Login')}
                                </SubmitButton>
                            </FormButtonWrapper>

                            <div className="mb-4 whitespace-nowrap">
                                <ExtendedNextLink href={resetPasswordUrl}>{t('Lost your password?')}</ExtendedNextLink>
                            </div>

                            {socialNetworkLoginConfig.length > 0 && (
                                <SocialNetworkLogin
                                    shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
                                    socialNetworks={socialNetworkLoginConfig}
                                />
                            )}
                        </FormButtonWrapper>
                    </FormBlockWrapper>
                </FormContentWrapper>
            </Form>
        </FormProvider>
    );
};
