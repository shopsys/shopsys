import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const ContactInformationCompany: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta();

    return (
        <>
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.companyName.name}
                textInputProps={{
                    disabled: formMeta.fields.customer.disabled,
                    label: formMeta.fields.companyName.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'organization',
                    onChange: (event) => updateContactInformation({ companyName: event.currentTarget.value }),
                }}
            />

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.companyNumber.name}
                    width="half"
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
                    width="half"
                    textInputProps={{
                        disabled: formMeta.fields.customer.disabled,
                        label: formMeta.fields.companyTaxNumber.label,
                        required: false,
                        type: 'text',
                        onChange: (event) => updateContactInformation({ companyTaxNumber: event.currentTarget.value }),
                    }}
                />
            </FormColumn>
        </>
    );
};
