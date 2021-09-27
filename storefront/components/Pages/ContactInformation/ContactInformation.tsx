import { FC, useState } from 'react';
import ContactInformationContent from './ContactInformationContent';
import ContactInformationEmail from 'components/Pages/ContactInformation/ContactInformationEmail';
import { Controller } from 'react-hook-form';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import { getContactInformationFormResolver } from './ContactInformationFormResolver';
import Webline from 'components/Layout/Webline';

const ContactInformation: FC = () => {
    const [isEmailEntered, setIsEmailEntered] = useState(false);

    return (
        <Webline>
            <Form
                onSubmitHandler={() => console.log('submit')}
                onSuccessHandler={() => console.log('success')}
                resolver={getContactInformationFormResolver()}
                defaultValues={{
                    email: '',
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
            </Form>
        </Webline>
    );
};

/* @component */
export default ContactInformation;
