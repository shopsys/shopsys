import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const ContactInformationCompany: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Company data')}</FormHeading>
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.companyName.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                textInputProps={{
                    disabled: formMeta.fields.customer.disabled,
                    label: formMeta.fields.companyName.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'organization',
                    onChange: (event) => updateContactInformation({ companyName: event.currentTarget.value }),
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.companyNumber.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                textInputProps={{
                    disabled: formMeta.fields.customer.disabled,
                    label: formMeta.fields.companyNumber.label,
                    required: true,
                    type: 'text',
                    onChange: (event) => updateContactInformation({ companyNumber: event.currentTarget.value }),
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.companyTaxNumber.name}
                render={(textInput) => <FormLine>{textInput}</FormLine>}
                textInputProps={{
                    disabled: formMeta.fields.customer.disabled,
                    label: formMeta.fields.companyTaxNumber.label,
                    required: false,
                    type: 'text',
                    onChange: (event) => updateContactInformation({ companyTaxNumber: event.currentTarget.value }),
                }}
            />
        </FormBlockWrapper>
    );
};
