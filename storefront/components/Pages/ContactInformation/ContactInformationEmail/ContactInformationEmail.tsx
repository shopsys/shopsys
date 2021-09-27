import { FC, useEffect } from 'react';
import { FieldError } from 'react-hook-form';
import FormLineError from 'components/Forms/Lib/FormLineError';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ContactInformationEmailProps = {
    isTouched: boolean;
    invalid: boolean;
    error?: FieldError;
    field: any;
    setIsEmailEntered: (p: boolean) => void;
};

const ContactInformationEmail: FC<ContactInformationEmailProps> = (props) => {
    const t = useTypedTranslationFunction();

    useEffect(() => {
        if (props.field.value.length > 1 && props.invalid === false) {
            props.setIsEmailEntered(true);
        } else {
            props.setIsEmailEntered(false);
        }
    }, [props.field.value, props.invalid]);

    return (
        <>
            <TextInput
                id="contactInformation_form-email"
                name="email"
                label={t('Your e-mail')}
                required={true}
                type="text"
                isTouched={props.isTouched}
                hasError={props.invalid}
                fieldRef={props.field}
            />
            <FormLineError error={props.error} inputType="text-input" />
        </>
    );
};

/* @component */
export default ContactInformationEmail;
