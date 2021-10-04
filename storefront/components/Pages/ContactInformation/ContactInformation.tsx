import { ContactInformationTextStyled, ContactInformationTextWrapperStyled } from './ContactInformation.style';
import { Controller, useFormContext } from 'react-hook-form';
import { FC, useState } from 'react';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import ContactInformationContent from './ContactInformationContent';
import ContactInformationEmail from 'components/Pages/ContactInformation/ContactInformationEmail';
import FormLine from 'components/Forms/Lib/FormLine';
import OrderAction from 'components/Blocks/OrderAction';
import { Trans } from 'react-i18next';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformation: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext();
    const [isEmailEntered, setIsEmailEntered] = useState(false);

    return (
        <>
            <FormLine bottomGap={true} lg="65%">
                <Controller
                    name="email"
                    render={({ fieldState: { isTouched, invalid, error }, field, formState }) => (
                        <ContactInformationEmail
                            isTouched={isTouched}
                            invalid={invalid}
                            error={error}
                            field={field}
                            setIsEmailEntered={setIsEmailEntered}
                        />
                    )}
                />
            </FormLine>
            <ContactInformationContent isEmailEntered={isEmailEntered} />
            <ContactInformationTextWrapperStyled isEmailEntered={isEmailEntered}>
                <ContactInformationTextStyled>
                    <Trans i18nKey="ContactInformationInfo">
                        By clicking on the Send order button, you agree with <a href="#">terms and conditions</a> of the
                        e-shop and with the <a href="#">processing of privacy policy</a>.
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
        </>
    );
};

/* @component */
export default ContactInformation;
