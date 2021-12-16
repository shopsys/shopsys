import {
    ButtonWrapperStyled,
    ContentSectionStyled,
    LoginProfileIconStyled,
    LoginProfileStyled,
    LoginProfileTextStrongStyled,
    LoginProfileTextStyled,
} from './Registration.style';
import { Controller, FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { initCartInputCookie, updateCartInputCookie } from 'helpers/Cookies';
import { RegistrationFormType, useRegistrationForm, useRegistrationFormMeta } from './formMeta';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import Address from './Address';
import Button from 'components/Forms/Button';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import Company from './Company';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Image from 'next/image';
import Password from './Password';
import { setTokensToCookie } from 'utils/Auth/TokensFromCookies';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import SimpleLayout from 'components/Layout/SimpleLayout';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import User from './User';
import { userActions } from 'redux/slices/user';
import { useRegistrationMutationApi } from 'graphql/generated';
import { useRouter } from 'next/router';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Registration: FC = () => {
    const t = useTypedTranslationFunction();
    const [registerResult, register] = useRegistrationMutationApi();
    const { url } = useShopsysSelector((state) => state.domain);
    const [RegistrationUrl] = useGetInternationalizedStaticUrls(['/registration'], url);
    const [formProviderMethods, defaultValues] = useRegistrationForm();
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    const router = useRouter();
    const dispatch = useShopsysDispatch();

    useHandleFormErrors(registerResult.error, formProviderMethods, formMeta.messages.error);
    useHandleFormSuccessfulSubmit(
        registerResult,
        formProviderMethods,
        defaultValues,
        () => {
            const accessToken = registerResult.data?.Register.accessToken;
            const refreshToken = registerResult.data?.Register.refreshToken;

            if (accessToken !== undefined && refreshToken !== undefined) {
                dispatch(userActions.setIsUserLoggedIn(true));
                updateCartInputCookie(initCartInputCookie(true));
                setTokensToCookie(accessToken, refreshToken);
                showSuccessMessage(formMeta.messages.successAndLogged);
            } else {
                showSuccessMessage(formMeta.messages.success);
            }

            router.push('/');
        },
        { blur: true, reset: true },
    );

    const onRegistrationHandler: SubmitHandler<RegistrationFormType> = async (data, event) => {
        event?.preventDefault();
        await register({
            ...data,
            password: data.passwordFirst,
            country: data.country.value,
            companyCustomer: data.customer === 'companyCustomer',
        });
    };

    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    return (
        <>
            <SimpleLayout heading={t('Registration')} breadcrumb={[{ name: t('Registration'), slug: RegistrationUrl }]}>
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onRegistrationHandler)}>
                        <ContentSectionStyled>
                            <LoginProfileStyled>
                                <LoginProfileTextStyled>
                                    <LoginProfileTextStrongStyled>
                                        {t("Don't have an account yet? Register.")}
                                    </LoginProfileTextStrongStyled>
                                    {t('You will shop with us as')}:
                                </LoginProfileTextStyled>
                                <LoginProfileIconStyled>
                                    <Image
                                        src="/images/qmark.png"
                                        height={120}
                                        width={75}
                                        alt={t("Don't have an account yet? Register.")}
                                    />
                                </LoginProfileIconStyled>
                            </LoginProfileStyled>
                        </ContentSectionStyled>

                        <ContentSectionStyled>
                            <User />
                        </ContentSectionStyled>

                        {customerValue === 'companyCustomer' && (
                            <ContentSectionStyled>
                                <Company />
                            </ContentSectionStyled>
                        )}

                        <ContentSectionStyled>
                            <Password />
                        </ContentSectionStyled>

                        <ContentSectionStyled>
                            <Address />
                        </ContentSectionStyled>

                        <Controller
                            name={formMeta.fields.gdprAgreement.name}
                            render={({ field, fieldState: { error } }) => (
                                <ChoiceFormLine>
                                    <Checkbox
                                        id={formMeta.formName + '-' + formMeta.fields.gdprAgreement.name}
                                        name={formMeta.fields.gdprAgreement.name}
                                        label={formMeta.fields.gdprAgreement.label}
                                        fieldRef={field}
                                        required={true}
                                    />
                                    <FormLineError error={error} inputType="checkbox" />
                                </ChoiceFormLine>
                            )}
                        />

                        <ChoiceFormLine>
                            <Controller
                                name={formMeta.fields.newsletterSubscription.name}
                                render={({ field }) => (
                                    <Checkbox
                                        id={formMeta.formName + '-' + formMeta.fields.newsletterSubscription.name}
                                        name={formMeta.fields.newsletterSubscription.name}
                                        label={formMeta.fields.newsletterSubscription.label}
                                        fieldRef={field}
                                    />
                                )}
                            />
                        </ChoiceFormLine>

                        <ButtonWrapperStyled>
                            <Button type="submit">{t('Sign up')}</Button>
                        </ButtonWrapperStyled>
                    </Form>
                </FormProvider>
            </SimpleLayout>

            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
            />
        </>
    );
};

export default Registration;
