import { Heading } from 'components/Basic/Heading/Heading';
import { FormColumn } from 'components/Forms/Lib/FormColumn/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { useFormContext } from 'react-hook-form';
import { useShopsysDispatch } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';

export const ContactInformationUser: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);

    return (
        <>
            <Heading type="h3">{t('Customer information')}</Heading>
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.telephone.name}
                render={(textInput) => (
                    <FormLine bottomGap lg="65%">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.telephone.label,
                    required: true,
                    type: 'text',
                    onBlur: (event) => dispatch(contactInformationActions.setTelephone(event.currentTarget.value)),
                }}
            />
            <FormColumn lg="65%">
                <TextInputControlled
                    control={formProviderMethods.control}
                    name={formMeta.fields.firstName.name}
                    render={(textInput) => (
                        <FormLine bottomGap width="100%" lg="50%">
                            {textInput}
                        </FormLine>
                    )}
                    formName={formMeta.formName}
                    textInputProps={{
                        label: formMeta.fields.firstName.label,
                        required: true,
                        type: 'text',
                        onBlur: (event) => dispatch(contactInformationActions.setFirstName(event.currentTarget.value)),
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    name={formMeta.fields.lastName.name}
                    render={(textInput) => (
                        <FormLine bottomGap width="100%" lg="50%">
                            {textInput}
                        </FormLine>
                    )}
                    formName={formMeta.formName}
                    textInputProps={{
                        label: formMeta.fields.lastName.label,
                        required: true,
                        type: 'text',
                        onBlur: (event) => dispatch(contactInformationActions.setLastName(event.currentTarget.value)),
                    }}
                />
            </FormColumn>
        </>
    );
};
