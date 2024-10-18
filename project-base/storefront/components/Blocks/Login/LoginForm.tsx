import { useLoginForm, useLoginFormMeta } from './loginFormMeta';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { SocialNetworkLogin } from 'components/Blocks/SocialNetworkLogin/SocialNetworkLogin';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { LoginFormType } from 'types/form';
import { useLogin } from 'utils/auth/useLogin';
import { blurInput } from 'utils/forms/blurInput';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export type LoginFormProps = {
    defaultEmail?: string;
    shouldOverwriteCustomerUserCart?: boolean;
    formContentWrapperClassName?: string;
};

export const LoginForm: FC<LoginFormProps> = ({
    defaultEmail,
    shouldOverwriteCustomerUserCart,
    formContentWrapperClassName,
}) => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);

    const { url } = useDomainConfig();
    const [resetPasswordUrl] = getInternationalizedStaticUrls(['/reset-password'], url);

    const [formProviderMethods] = useLoginForm(defaultEmail);
    const formMeta = useLoginFormMeta(formProviderMethods);
    const login = useLogin();
    const [{ data: settingsData }] = useSettingsQuery();

    const onLoginHandler: SubmitHandler<LoginFormType> = async (data) => {
        blurInput();

        const loginResponse = await login({
            email: data.email,
            password: data.password,
            previousCartUuid: cartUuid,
            shouldOverwriteCustomerUserCart,
        });

        handleFormErrors(
            loginResponse.error,
            formProviderMethods,
            t,
            undefined,
            undefined,
            GtmMessageOriginType.login_popup,
        );
    };

    return (
        <FormProvider {...formProviderMethods}>
            <Form className="flex w-full justify-center" onSubmit={formProviderMethods.handleSubmit(onLoginHandler)}>
                <FormContentWrapper className={formContentWrapperClassName}>
                    <FormBlockWrapper>
                        <FormHeading>{t('Log-in')}</FormHeading>

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

                            {settingsData?.settings?.socialNetworkLoginConfig !== undefined &&
                                settingsData.settings.socialNetworkLoginConfig.length > 0 && (
                                    <SocialNetworkLogin
                                        shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
                                        socialNetworks={settingsData.settings.socialNetworkLoginConfig}
                                    />
                                )}
                        </FormButtonWrapper>
                    </FormBlockWrapper>
                </FormContentWrapper>
            </Form>
        </FormProvider>
    );
};
