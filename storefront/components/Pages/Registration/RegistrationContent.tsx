import { Address } from './Address/Address';
import { Company } from './Company/Company';
import { useRegistrationForm, useRegistrationFormMeta } from './formMeta';
import { Password } from './Password/Password';
import {
    ButtonWrapperStyled,
    ContentSectionStyled,
    LoginProfileIconStyled,
    LoginProfileStyled,
    LoginProfileTextStrongStyled,
    LoginProfileTextStyled,
} from './RegistrationContent.style';
import { User } from './User/User';
import { Button } from 'components/Forms/Button/Button';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine/ChoiceFormLine';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { showInfoMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useRegistrationMutationApi } from 'graphql/generated';
import { setTokensToCookie } from 'helpers/auth/tokens';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Image from 'next/image';
import { FC, useCallback } from 'react';
import { FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { RegistrationFormType } from 'types/form';

type RegistrationContentProps = {
    breadcrumbs: BreadcrumbItemType[];
};

export const RegistrationContent: FC<RegistrationContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const [, register] = useRegistrationMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const [formProviderMethods, defaultValues] = useRegistrationForm();
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);

    const onRegistrationHandler = useCallback<SubmitHandler<RegistrationFormType>>(
        async (data) => {
            blurInput();
            const registerResult = await register({
                ...data,
                password: data.passwordFirst,
                previousCartUuid: cartUuid,
                country: data.country.value,
                companyCustomer: data.customer === 'companyCustomer',
            });

            if (registerResult.data?.Register !== undefined) {
                const accessToken = registerResult.data.Register.tokens.accessToken;
                const refreshToken = registerResult.data.Register.tokens.refreshToken;

                setTokensToCookie(accessToken, refreshToken);
                showSuccessMessage(formMeta.messages.successAndLogged);

                if (registerResult.data.Register.showCartMergeInfo === true) {
                    showInfoMessage(t('Your cart has been modified. Please check the changes.'));
                }

                window.location.href = '/';
            }

            handleFormErrors(registerResult.error, formProviderMethods, 'other', t, formMeta.messages.error);
            clearForm(registerResult.error, formProviderMethods, defaultValues);
        },
        [cartUuid, formMeta.messages, formProviderMethods, register, t, defaultValues],
    );

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

                        <CheckboxControlled
                            name={formMeta.fields.gdprAgreement.name}
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                            checkboxProps={{
                                label: formMeta.fields.gdprAgreement.label,
                            }}
                        />
                        <CheckboxControlled
                            name={formMeta.fields.newsletterSubscription.name}
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                            checkboxProps={{
                                label: formMeta.fields.newsletterSubscription.label,
                            }}
                        />
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
