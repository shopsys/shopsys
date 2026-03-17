import { ContactInformationBillingAddress } from './FormBlocks/ContactInformationBillingAddress';
import { FormBlockWrapper } from 'components/Forms/Form/Form';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { ContactInformationDeliveryAddress } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationDeliveryAddress';
import { ContactInformationPersonalInformation } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationPersonalInformation';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const ContactInformationFormContent: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta();

    return (
        <>
            <ContactInformationPersonalInformation />

            <ContactInformationBillingAddress />

            <ContactInformationDeliveryAddress />

            <FormBlockWrapper>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.note.name}
                    textInputProps={{
                        label: formMeta.fields.note.label,
                        type: 'text',
                        autoComplete: 'note',
                        onChange: (event) => updateContactInformation({ note: event.currentTarget.value }),
                    }}
                />
            </FormBlockWrapper>
        </>
    );
};
