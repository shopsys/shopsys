import * as Yup from 'yup';
import { FC, useState } from 'react';
import ContactInformationEmail from 'components/Pages/ContactInformation/ContactInformationEmail';
import { Controller } from 'react-hook-form';
import Form from 'components/Forms/Form';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import { TFunction } from 'next-i18next';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';
import { yupResolver } from '@hookform/resolvers/yup';

const getContactInformationFormResolver = (t: TFunction) => {
    return yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
        }),
    );
};

const ContactInformation: FC = () => {
    const [isEmailEntered, setIsEmailEntered] = useState(false);
    const t = useTypedTranslationFunction();

    return (
        <Webline>
            <Form
                onSubmitHandler={() => console.log('submit')}
                onSuccessHandler={() => console.log('success')}
                resolver={getContactInformationFormResolver(t)}
                defaultValues={{ email: '' }}
            >
                <FormColumn>
                    <FormLine Lg="65%">
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
                </FormColumn>
                {isEmailEntered && 'Content ktory sa otvori po spravnom zadani emailu'}
            </Form>
        </Webline>
    );
};

/* @component */
export default ContactInformation;
