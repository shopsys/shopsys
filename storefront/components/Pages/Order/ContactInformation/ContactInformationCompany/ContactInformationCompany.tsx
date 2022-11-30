import { Heading } from 'components/Basic/Heading/Heading';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useShopsysDispatch } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';

export const ContactInformationCompany: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [companyNameValue, companyNumberValue, companyTaxNumberValue] = useWatch({
        name: [
            formMeta.fields.companyName.name,
            formMeta.fields.companyNumber.name,
            formMeta.fields.companyTaxNumber.name,
        ],
        control: formProviderMethods.control,
    });

    return (
        <>
            <Heading type="h3">{t('Company data')}</Heading>
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.companyName.name}
                render={(textInput) => (
                    <FormLine bottomGap lg="65%">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.companyName.label,
                    required: true,
                    type: 'text',
                    onBlur: () => dispatch(contactInformationActions.setCompanyName(companyNameValue)),
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.companyNumber.name}
                render={(textInput) => (
                    <FormLine bottomGap lg="65%">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.companyNumber.label,
                    required: true,
                    type: 'text',
                    onBlur: () => dispatch(contactInformationActions.setCompanyNumber(companyNumberValue)),
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.companyTaxNumber.name}
                render={(textInput) => (
                    <FormLine bottomGap lg="65%">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.companyTaxNumber.label,
                    required: false,
                    type: 'text',
                    onBlur: () => dispatch(contactInformationActions.setCompanyTaxNumber(companyTaxNumberValue)),
                }}
            />
        </>
    );
};
