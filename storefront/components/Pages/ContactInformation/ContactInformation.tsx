import { ContactInformationTextStyled, ContactInformationTextWrapperStyled } from './ContactInformation.style';
import { FC, useState } from 'react';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import ContactInformationContent from './ContactInformationContent';
import ContactInformationEmail from 'components/Pages/ContactInformation/ContactInformationEmail';
import { Controller } from 'react-hook-form';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import { getContactInformationFormResolver } from './ContactInformationFormResolver';
import { Trans } from 'react-i18next';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const ContactInformation: FC = () => {
    const t = useTypedTranslationFunction();
    const [isEmailEntered, setIsEmailEntered] = useState(false);

    return (
        <Webline>
            <Form
                onSubmitHandler={() => console.log('submit')}
                onSuccessHandler={() => console.log('success')}
                resolver={getContactInformationFormResolver()}
                defaultValues={{
                    email: '',
                    register: false,
                    passwordFirst: '',
                    passwordSecond: '',
                    customer: 'commonCustomer',
                    telephone: '',
                    firstName: '',
                    lastName: '',
                    street: '',
                    city: '',
                    postcode: '',
                    country: 'Slovensko',
                    companyName: '',
                    companyNumber: '',
                    companyTaxNumber: '',
                    deliveryAddress: false,
                    deliveryFirstName: '',
                    deliveryLastName: '',
                    deliveryCompanyName: '',
                    deliveryTelephone: '',
                    deliveryStreet: '',
                    deliveryCity: '',
                    deliveryPostcode: '',
                    deliveryCountry: 'Slovensko',
                    newsletterSubscription: false,
                }}
            >
                <FormLine bottomGap={true} Lg="65%">
                    <Controller
                        name="email"
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
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
                            By clicking on the Send order button, you agree with <a href="#">terms and conditions</a> of
                            the e-shop and with the <a href="#">processing of privacy policy</a>.
                        </Trans>
                    </ContactInformationTextStyled>
                    <ChoiceFormLine>
                        <Controller
                            name="newsletterSubscription"
                            render={({ field }) => (
                                <Checkbox
                                    id="newsletter_form-newsletterSubscription"
                                    name={field.name}
                                    label={t('I want to subscribe to the newsletter')}
                                    fieldRef={field}
                                />
                            )}
                        />
                    </ChoiceFormLine>
                </ContactInformationTextWrapperStyled>
            </Form>
        </Webline>
    );
};

/* @component */
export default ContactInformation;
