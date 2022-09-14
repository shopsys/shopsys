import { useRegistrationAfterOrderForm, useRegistrationAfterOrderFormMeta } from './formMeta';
import {
    RegistrationBenefitsListItem,
    RegistrationFormColumnStyled,
    RegistrationFormItemStyled,
    RegistrationFormStyled,
    RegistrationHeadingStyled,
    RegistrationMessageColumnStyled,
    RegistrationStyled,
} from './Registration.style';
import { Button } from 'components/Forms/Button/Button';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine/ChoiceFormLine';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { Webline } from 'components/Layout/Webline/Webline';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useRegistrationMutationApi } from 'graphql/generated';
import { setTokensToCookie } from 'helpers/auth/tokens';
import { useHandleErrorPopupVisibility } from 'hooks/forms/useHandleErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import Trans from 'next-translate/Trans';
import { FC } from 'react';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { useShopsysDispatch } from 'redux/main';
import { userActions } from 'redux/slices/user';
import { RegistrationAfterOrderFormType } from 'types/form';

const TEST_IDENTIFIER = 'pages-orderconfirmation-registration-create-account';

export const Registration: FC = () => {
    const dispatch = useShopsysDispatch();
    const contactInformation = useCurrentUserContactInformation();
    const [, register] = useRegistrationMutationApi();
    const t = useTypedTranslationFunction();
    const [formProviderMethods] = useRegistrationAfterOrderForm();
    const formMeta = useRegistrationAfterOrderFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    useEffectOnce(() => {
        return () => {
            dispatch(userActions.setOrderConfirmationAccess(false));
        };
    });

    const onRegistrationSubmitHandler: SubmitHandler<RegistrationAfterOrderFormType> = async (data) => {
        const registerResult = await register({
            ...data,
            ...contactInformation,
            country: contactInformation.country.value,
            companyCustomer: contactInformation.customer === 'companyCustomer',
            previousCartUuid: null,
        });

        if (registerResult.data !== undefined && registerResult.error === undefined) {
            const accessToken = registerResult.data.Register.tokens.accessToken;
            const refreshToken = registerResult.data.Register.tokens.refreshToken;

            setTokensToCookie(accessToken, refreshToken);
            showSuccessMessage(t('Your account has been created and you are logged in now'));

            window.location.href = '/';
        } else if (registerResult.error !== undefined) {
            const validationErrors = getUserFriendlyErrors(registerResult.error, t).userError?.validation;
            for (const fieldName in validationErrors) {
                showErrorMessage(validationErrors[fieldName].message, 'purchase');
            }
        }
    };

    return (
        <>
            <Webline>
                <RegistrationStyled>
                    <RegistrationMessageColumnStyled>
                        <RegistrationHeadingStyled type="h2">
                            <Trans
                                i18nKey="Finish registration to loyalty program."
                                components={{ 0: <br />, 1: <strong /> }}
                            />
                        </RegistrationHeadingStyled>
                        <ul>
                            <RegistrationBenefitsListItem>
                                {t('You will have an overview of your orders and complaints')}
                            </RegistrationBenefitsListItem>
                            <RegistrationBenefitsListItem>
                                {t('Collecting points with every order')}
                            </RegistrationBenefitsListItem>
                            <RegistrationBenefitsListItem>
                                {t('Possibility of purchases for better prices')}
                            </RegistrationBenefitsListItem>
                            <RegistrationBenefitsListItem>
                                {t('Exclusive products as a part of the loyalty program')}
                            </RegistrationBenefitsListItem>
                        </ul>
                    </RegistrationMessageColumnStyled>
                    <RegistrationFormColumnStyled>
                        <RegistrationFormStyled>
                            <Form onSubmit={formProviderMethods.handleSubmit(onRegistrationSubmitHandler)}>
                                <FormProvider {...formProviderMethods}>
                                    <Controller
                                        name={formMeta.fields.password.name}
                                        render={({ field, fieldState: { error, invalid, isTouched } }) => (
                                            <RegistrationFormItemStyled>
                                                <FormLine>
                                                    <TextInput
                                                        id={formMeta.formName + '-' + formMeta.fields.password.name}
                                                        name={formMeta.fields.password.name}
                                                        label={formMeta.fields.password.label}
                                                        type="password"
                                                        fieldRef={field}
                                                        required
                                                        isTouched={isTouched}
                                                        hasError={invalid}
                                                    />
                                                    <FormLineError
                                                        inputType="text-input-password"
                                                        error={error}
                                                        testIdentifier={
                                                            formMeta.formName +
                                                            '-' +
                                                            formMeta.fields.password.name +
                                                            '-error'
                                                        }
                                                    />
                                                </FormLine>
                                            </RegistrationFormItemStyled>
                                        )}
                                    />
                                    <Controller
                                        name={formMeta.fields.privacyPolicy.name}
                                        render={({ field, fieldState: { error } }) => (
                                            <RegistrationFormItemStyled>
                                                <ChoiceFormLine>
                                                    <Checkbox
                                                        name={formMeta.fields.privacyPolicy.name}
                                                        label={formMeta.fields.privacyPolicy.label}
                                                        fieldRef={field}
                                                        required
                                                    />
                                                    <FormLineError
                                                        inputType="checkbox"
                                                        error={error}
                                                        testIdentifier={
                                                            formMeta.formName +
                                                            '-' +
                                                            formMeta.fields.privacyPolicy.name +
                                                            '-error'
                                                        }
                                                    />
                                                </ChoiceFormLine>
                                            </RegistrationFormItemStyled>
                                        )}
                                    />
                                    <Button
                                        testIdentifier={TEST_IDENTIFIER}
                                        type="submit"
                                        variant="primary"
                                        borderRadius="big"
                                        style={{ width: '100%' }}
                                        hasDisabledLook={!formProviderMethods.formState.isValid}
                                    >
                                        {t('Create account')}
                                    </Button>
                                </FormProvider>
                            </Form>
                        </RegistrationFormStyled>
                    </RegistrationFormColumnStyled>
                </RegistrationStyled>
            </Webline>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
                origin="purchase"
            />
        </>
    );
};
