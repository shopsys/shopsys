import { Controller, useWatch } from 'react-hook-form';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { FC } from 'react';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Heading from 'components/Basic/Heading';
import TextInput from 'components/Forms/TextInput';
import { useShopsysDispatch } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationUser: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const telephoneValue = useWatch({ name: 'telephone' });
    const firstNameValue = useWatch({ name: 'firstName' });
    const lastNameValue = useWatch({ name: 'lastName' });

    return (
        <>
            <Heading type="h3">{t('Customer information')}</Heading>
            <FormLine bottomGap={true} lg="65%">
                <Controller
                    name="telephone"
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id="contactInformation_form-telephone"
                                name="telephone"
                                label={t('Telephone')}
                                required={true}
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                                onBlurCapture={() => dispatch(contactInformationActions.setTelephone(telephoneValue))}
                            />
                            <FormLineError error={error} inputType="text-input" />
                        </>
                    )}
                />
            </FormLine>

            <FormColumn lg="65%">
                <FormLine bottomGap={true} width="100%" lg="50%">
                    <Controller
                        name="firstName"
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id="contactInformation_form-firstName"
                                    name="firstName"
                                    label={t('First name')}
                                    required={true}
                                    type="text"
                                    isTouched={isTouched}
                                    hasError={invalid}
                                    fieldRef={field}
                                    onBlurCapture={() =>
                                        dispatch(contactInformationActions.setFirstName(firstNameValue))
                                    }
                                />
                                <FormLineError error={error} inputType="text-input" />
                            </>
                        )}
                    />
                </FormLine>
                <FormLine bottomGap={true} width="100%" lg="50%">
                    <Controller
                        name="lastName"
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id="contactInformation_form-lastName"
                                    name="lastName"
                                    label={t('Last name')}
                                    required={true}
                                    type="text"
                                    isTouched={isTouched}
                                    hasError={invalid}
                                    fieldRef={field}
                                    onBlurCapture={() => dispatch(contactInformationActions.setLastName(lastNameValue))}
                                />
                                <FormLineError error={error} inputType="text-input" />
                            </>
                        )}
                    />
                </FormLine>
            </FormColumn>
        </>
    );
};

/* @component */
export default ContactInformationUser;
