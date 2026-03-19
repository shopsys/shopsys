import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const ContactInformationDeliveryPickUpForm = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta();

    return (
        <div className="flex flex-col gap-5">
            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.deliveryFirstName.name}
                    width="half"
                    textInputProps={{
                        label: formMeta.fields.deliveryFirstName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'given-name',
                        onChange: (event) => {
                            updateContactInformation({
                                deliveryFirstName: event.currentTarget.value,
                            });
                        },
                    }}
                />

                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.deliveryLastName.name}
                    width="half"
                    textInputProps={{
                        label: formMeta.fields.deliveryLastName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'family-name',
                        onChange: (event) =>
                            updateContactInformation({
                                deliveryLastName: event.currentTarget.value,
                            }),
                    }}
                />
            </FormColumn>

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.deliveryTelephone.name}
                    width="half"
                    textInputProps={{
                        label: formMeta.fields.deliveryTelephone.label,
                        required: false,
                        type: 'tel',
                        autoComplete: 'tel',
                        onChange: (event) =>
                            updateContactInformation({
                                deliveryTelephone: event.currentTarget.value,
                            }),
                    }}
                />
            </FormColumn>
        </div>
    );
};
