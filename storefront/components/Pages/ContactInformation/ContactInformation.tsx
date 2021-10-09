import {
    ContactInformationCartStyled,
    ContactInformationInfoStyled,
    ContactInformationStyled,
    ContactInformationTextStyled,
    ContactInformationTextWrapperStyled,
} from './ContactInformation.style';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import ContactInformationContent from './ContactInformationContent';
import { FC } from 'react';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSummary from 'components/Blocks/OrderSummary';
import TextInput from 'components/Forms/TextInput';
import { Trans } from 'react-i18next';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformation: FC = () => {
    const cart = useShopsysSelector((state) => state.user.cart);
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext();
    const emailInput = useWatch({ name: 'email' });
    const isEmailValid = emailInput.length >= 5 && formProviderMethods.formState.errors.email === undefined;

    return (
        <ContactInformationStyled>
            <ContactInformationInfoStyled>
                <FormLine bottomGap={true} lg="65%">
                    <Controller
                        name="email"
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id="contactInformation_form-email"
                                    name="email"
                                    label={t('Your e-mail')}
                                    required={true}
                                    type="text"
                                    isTouched={isTouched}
                                    hasError={invalid}
                                    fieldRef={field}
                                />
                                <FormLineError error={error} inputType="text-input" />
                            </>
                        )}
                    />
                </FormLine>
                <ContactInformationContent isEmailEntered={isEmailValid} />
                <ContactInformationTextWrapperStyled isEmailEntered={isEmailValid}>
                    <ContactInformationTextStyled>
                        <Trans i18nKey="ContactInformationInfo">
                            By clicking on the Send order button, you agree with <a href="#">terms and conditions</a> of
                            the e-shop and with the <a href="#">processing of privacy policy</a>.
                        </Trans>
                    </ContactInformationTextStyled>
                    <ChoiceFormLine>
                        <Controller
                            name="newsletterSubscription"
                            render={({ field }) => (
                                <Checkbox
                                    id="contactInformation_form-newsletterSubscription"
                                    name={field.name}
                                    label={t('I want to subscribe to the newsletter')}
                                    fieldRef={field}
                                />
                            )}
                        />
                    </ChoiceFormLine>
                </ContactInformationTextWrapperStyled>
                <OrderAction
                    activeStep={3}
                    buttonBack={t('Back')}
                    buttonNext={t('Submit order')}
                    isDisabled={!formProviderMethods.formState.isValid}
                />
            </ContactInformationInfoStyled>
            <ContactInformationCartStyled>
                <OrderSummary />
            </ContactInformationCartStyled>
        </ContactInformationStyled>
    );
};

/* @component */
export default ContactInformation;
