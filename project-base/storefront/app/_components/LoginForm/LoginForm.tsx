'use client';

import { loginAction } from 'app/_actions/loginAction';
import { SocialNetworkLogin } from 'app/_components/SocialNetworkLogin/SocialNetworkLogin';
import { useHandleActionsAfterLogin } from 'app/_hooks/useHandleActionsAfterLogin';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { useLoginForm, useLoginFormMeta } from 'components/Blocks/Login/loginFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeLoginTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { LoginFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export type LoginFormProps = {
    defaultEmail?: string;
    shouldOverwriteCustomerUserCart?: boolean;
    formContentWrapperClassName?: string;
    formHeading: string;
    socialNetworks: TypeLoginTypeEnum[] | undefined;
};

export const LoginForm: FC<LoginFormProps> = ({
    defaultEmail,
    shouldOverwriteCustomerUserCart,
    formContentWrapperClassName,
    formHeading,
    socialNetworks,
}) => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const handleActionsAfterLogin = useHandleActionsAfterLogin();

    const { url } = useDomainConfig();
    const [resetPasswordUrl] = getInternationalizedStaticUrls(['/reset-password'], url);

    const [formProviderMethods] = useLoginForm(defaultEmail);
    const formMeta = useLoginFormMeta(formProviderMethods);

    const onLoginHandler: SubmitHandler<LoginFormType> = async (data) => {
        blurInput();

        const loginResponse = await loginAction({
            email: data.email,
            password: data.password,
            previousCartUuid: cartUuid,
            productListsUuids: Object.values(productListUuids),
            shouldOverwriteCustomerUserCart,
        });

        if (loginResponse.error === undefined) {
            handleActionsAfterLogin(loginResponse.showCartMergeInfo, undefined);
        }

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

                            {socialNetworks && (
                                <SocialNetworkLogin
                                    shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
                                    socialNetworks={socialNetworks}
                                />
                            )}
                        </FormButtonWrapper>
                    </FormBlockWrapper>
                </FormContentWrapper>
            </Form>
        </FormProvider>
    );
};
