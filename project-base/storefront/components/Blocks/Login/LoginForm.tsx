import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { SocialNetworkLogin } from 'components/Blocks/SocialNetworkLogin/SocialNetworkLogin';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { LoginFormType } from 'types/form';
import { useLogin } from 'utils/auth/useLogin';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useLoginForm, useLoginFormMeta } from './loginFormMeta';

export type LoginFormProps = {
    defaultEmail?: string;
    shouldOverwriteCustomerUserCart?: boolean;
    formWrapperClassName?: string;
    formContentWrapperClassName?: string;
};

export const LoginForm: FC<LoginFormProps> = ({
    defaultEmail,
    shouldOverwriteCustomerUserCart,
    formWrapperClassName,
    formContentWrapperClassName,
}) => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);

    const { url } = useDomainConfig();
    const [resetPasswordUrl] = getInternationalizedStaticUrls(['/reset-password'], url);

    const [formProviderMethods] = useLoginForm(defaultEmail);
    const formMeta = useLoginFormMeta();
    const login = useLogin();
    const [{ data: settingsData }] = useSettingsQuery();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        gtmOrigin: GtmMessageOriginType.login_popup,
    });

    const onLoginHandler: SubmitHandler<LoginFormType> = async (data) => {
        blurInput();

        const loginResponse = await login({
            email: data.email,
            password: data.password,
            previousCartUuid: cartUuid,
            shouldOverwriteCustomerUserCart,
        });

        handleError(loginResponse.error);
    };

    return (
        <div className={formWrapperClassName}>
            <p className="sr-only" id="login-form-description">
                {t('Login form for logging in to your account. Please fill in your email address and password.')}
            </p>

            <FormProvider {...formProviderMethods}>
                <Form
                    className="flex w-full justify-center"
                    formName={formMeta.formName}
                    onSubmit={formProviderMethods.handleSubmit(onLoginHandler)}
                >
                    <FormContentWrapper className={formContentWrapperClassName}>
                        <TextInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.email.name}
                            textInputProps={{
                                label: formMeta.fields.email.label,
                                required: true,
                                type: 'email',
                                autoComplete: 'email',
                                'aria-describedby': 'login-form-description',
                            }}
                        />

                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.password.name}
                            passwordInputProps={{
                                label: formMeta.fields.password.label,
                                autoComplete: 'current-password',
                            }}
                        />

                        <FormButtonWrapper className="mt-0 flex items-center justify-between">
                            <div className="whitespace-nowrap">
                                <ExtendedNextLink
                                    aria-label={t('Go to reset password page', { ns: 'accessibility' })}
                                    href={resetPasswordUrl}
                                    skeletonType="reset-password"
                                >
                                    {t('Lost your password?')}
                                </ExtendedNextLink>
                            </div>
                            <SubmitButton size="large" tid={TIDs.login_form_submit_button}>
                                {t('Login')}
                            </SubmitButton>
                        </FormButtonWrapper>

                        {settingsData?.settings?.socialNetworkLoginConfig !== undefined &&
                            settingsData.settings.socialNetworkLoginConfig.length > 0 && (
                                <>
                                    <div className="flex items-center gap-3 text-sm text-text-less">
                                        <div className="h-px flex-1 bg-background-accent-less" />
                                        <span>{t('or')}</span>
                                        <div className="h-px flex-1 bg-background-accent-less" />
                                    </div>

                                    <SocialNetworkLogin
                                        shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
                                        socialNetworks={settingsData.settings.socialNetworkLoginConfig}
                                    />
                                </>
                            )}
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </div>
    );
};
