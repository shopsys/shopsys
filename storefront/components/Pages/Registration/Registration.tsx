import Address from './Address';
import Company from './Company';
import { RegistrationFormType, useRegistrationForm, useRegistrationFormMeta } from './formMeta';
import Password from './Password';
import {
    ButtonWrapperStyled,
    ContentSectionStyled,
    LoginProfileIconStyled,
    LoginProfileStyled,
    LoginProfileTextStrongStyled,
    LoginProfileTextStyled,
} from './Registration.style';
import User from './User';
import Button from 'components/Forms/Button';
import Checkbox from 'components/Forms/Checkbox';
import Form from 'components/Forms/Form';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { showInfoMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import SimpleLayout from 'components/Layout/SimpleLayout';
import { useRegistrationMutationApi } from 'graphql/generated';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Image from 'next/image';
import { FC } from 'react';
import { Controller, FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { setTokensToCookie } from 'utils/Auth/TokensFromCookies';

type RegistrationProps = {
    breadcrumbs: BreadcrumbItemType[];
};

const Registration: FC<RegistrationProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const [registerResult, register] = useRegistrationMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const [formProviderMethods, defaultValues] = useRegistrationForm();
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    useHandleFormErrors(registerResult.error, formProviderMethods, 'other', formMeta.messages.error);
    useHandleFormSuccessfulSubmit(
        registerResult,
        formProviderMethods,
        defaultValues,
        () => {
            const accessToken = registerResult.data?.Register.tokens.accessToken;
            const refreshToken = registerResult.data?.Register.tokens.refreshToken;

            if (accessToken !== undefined && refreshToken !== undefined) {
                setTokensToCookie(accessToken, refreshToken);
                showSuccessMessage(formMeta.messages.successAndLogged);

                if (registerResult.data?.Register.showCartMergeInfo === true) {
                    showInfoMessage(t('Your cart has been modified. Please check the changes.'));
                }
            } else {
                showSuccessMessage(formMeta.messages.success);
            }

            window.location.href = '/';
        },
        { blur: true, reset: true },
    );

    const onRegistrationHandler: SubmitHandler<RegistrationFormType> = async (data, event) => {
        event?.preventDefault();
        await register({
            ...data,
            password: data.passwordFirst,
            previousCartUuid: cartUuid,
            country: data.country.value,
            companyCustomer: data.customer === 'companyCustomer',
        });
    };

    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    return (
        <>
            <SimpleLayout heading={t('Registration')} breadcrumb={breadcrumbs}>
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
                                    <FormLineError
                                        error={error}
                                        inputType="checkbox"
                                        data-testid={
                                            formMeta.formName + '-' + formMeta.fields.gdprAgreement.name + '-error'
                                        }
                                    />
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
                origin="other"
            />
        </>
    );
};

export default Registration;
