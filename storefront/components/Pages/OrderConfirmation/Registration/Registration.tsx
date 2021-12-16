import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { FC, useEffect } from 'react';
import {
    RegistrationBenefitsListItem,
    RegistrationFormColumnStyled,
    RegistrationFormItemStyled,
    RegistrationFormStyled,
    RegistrationHeadingStyled,
    RegistrationMessageColumnStyled,
    RegistrationStyled,
} from './Registration.style';
import { useRegistrationAfterOrderForm, useRegistrationAfterOrderFormMeta } from './formMeta';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import Button from 'components/Forms/Button';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import { contactInformationActions } from 'redux/slices/contactInformation';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { RegistrationAfterOrderFormType } from 'types/form';
import { showErrorMessage } from 'components/Helpers/Toasts';
import TextInput from 'components/Forms/TextInput';
import { Trans } from 'react-i18next';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { userActions } from 'redux/slices/user';
import { useRegistrationMutationApi } from 'graphql/generated';
import { useRouter } from 'next/router';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const Registration: FC = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const contactInformation = useShopsysSelector((state) => state.contactInformation);
    const [registerResult, register] = useRegistrationMutationApi();
    const t = useTypedTranslationFunction();
    const [formProviderMethods] = useRegistrationAfterOrderForm();
    const formMeta = useRegistrationAfterOrderFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    useEffect(() => {
        return () => {
            dispatch(userActions.setOrderConfirmationAccess(false));
            dispatch(contactInformationActions.resetContactInformation());
        };
    }, []);

    useEffect(() => {
        if (registerResult.data !== undefined && registerResult.error === undefined) {
            router.push('/');
            return;
        }
        if (registerResult.error !== undefined) {
            const validationErrors = getUserFriendlyErrors(registerResult.error, t).userError?.validation;
            for (const fieldName in validationErrors) {
                showErrorMessage(validationErrors[fieldName].message);
            }
        }
    }, [registerResult.data, registerResult.error]);

    const onRegistrationSubmitHandler: SubmitHandler<RegistrationAfterOrderFormType> = async (data) => {
        await register({
            ...data,
            ...contactInformation,
            country: contactInformation.country.value,
            companyCustomer: contactInformation.customer === 'companyCustomer',
        });
    };

    return (
        <>
            <Webline>
                <RegistrationStyled>
                    <RegistrationMessageColumnStyled>
                        <RegistrationHeadingStyled type="h2">
                            <Trans i18nKey="Finish registration to loyalty program.">
                                Finish registration <br /> to
                                <strong>
                                    loyalty <br /> program
                                </strong>
                            </Trans>
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
                            <Form onSubmit={formProviderMethods.handleSubmit(onRegistrationSubmitHandler)} noValidate>
                                <FormProvider {...formProviderMethods}>
                                    <Controller
                                        name={formMeta.fields.password.name}
                                        render={({ field, fieldState: { error, invalid, isTouched } }) => (
                                            <RegistrationFormItemStyled>
                                                <FormLine>
                                                    <TextInput
                                                        name={formMeta.fields.password.name}
                                                        label={formMeta.fields.password.label}
                                                        type="password"
                                                        fieldRef={field}
                                                        required={true}
                                                        isTouched={isTouched}
                                                        hasError={invalid}
                                                    />
                                                    <FormLineError inputType="text-input-password" error={error} />
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
                                                        required={true}
                                                    />
                                                    <FormLineError inputType="checkbox" error={error} />
                                                </ChoiceFormLine>
                                            </RegistrationFormItemStyled>
                                        )}
                                    />
                                    <Button
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
            />
        </>
    );
};

export default Registration;
