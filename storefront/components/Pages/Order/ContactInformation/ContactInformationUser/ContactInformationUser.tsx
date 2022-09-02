import Heading from 'components/Basic/Heading';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import TextInput from 'components/Forms/TextInput';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { useShopsysDispatch } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';

const ContactInformationUser: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [telephoneValue, firstNameValue, lastNameValue] = useWatch({
        name: [formMeta.fields.telephone.name, formMeta.fields.firstName.name, formMeta.fields.lastName.name],
        control: formProviderMethods.control,
    });

    return (
        <>
            <Heading type="h3">{t('Customer information')}</Heading>
            <FormLine bottomGap={true} lg="65%">
                <Controller
                    name={formMeta.fields.telephone.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.telephone.name}
                                name={formMeta.fields.telephone.name}
                                label={formMeta.fields.telephone.label}
                                required={true}
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                                onBlurCapture={() => dispatch(contactInformationActions.setTelephone(telephoneValue))}
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                data-testid={formMeta.formName + '-' + formMeta.fields.telephone.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>

            <FormColumn lg="65%">
                <FormLine bottomGap={true} width="100%" lg="50%">
                    <Controller
                        name={formMeta.fields.firstName.name}
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id={formMeta.formName + '-' + formMeta.fields.firstName.name}
                                    name={formMeta.fields.firstName.name}
                                    label={formMeta.fields.firstName.label}
                                    required={true}
                                    type="text"
                                    isTouched={isTouched}
                                    hasError={invalid}
                                    fieldRef={field}
                                    onBlurCapture={() =>
                                        dispatch(contactInformationActions.setFirstName(firstNameValue))
                                    }
                                />
                                <FormLineError
                                    error={error}
                                    inputType="text-input"
                                    data-testid={formMeta.formName + '-' + formMeta.fields.firstName.name + '-error'}
                                />
                            </>
                        )}
                    />
                </FormLine>
                <FormLine bottomGap={true} width="100%" lg="50%">
                    <Controller
                        name={formMeta.fields.lastName.name}
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id={formMeta.formName + '-' + formMeta.fields.lastName.name}
                                    name={formMeta.fields.lastName.name}
                                    label={formMeta.fields.lastName.label}
                                    required={true}
                                    type="text"
                                    isTouched={isTouched}
                                    hasError={invalid}
                                    fieldRef={field}
                                    onBlurCapture={() => dispatch(contactInformationActions.setLastName(lastNameValue))}
                                />
                                <FormLineError
                                    error={error}
                                    inputType="text-input"
                                    data-testid={formMeta.formName + '-' + formMeta.fields.lastName.name + '-error'}
                                />
                            </>
                        )}
                    />
                </FormLine>
            </FormColumn>
        </>
    );
};

export default ContactInformationUser;
