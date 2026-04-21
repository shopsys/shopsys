import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { PhoneNumberInputControlled } from 'components/Forms/PhonePrefixSelect/PhoneNumberInputControlled';
import { CountrySelectControlled } from 'components/Forms/Select/CountrySelectControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';

export const ContactInformationDeliveryAddressForm = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta();
    const { pickupPlace } = useCurrentCart();

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

            <PhoneNumberInputControlled
                formName={formMeta.formName}
                formProviderMethods={formProviderMethods}
                isTelephoneRequired={false}
                prefixCountryCodeName={formMeta.fields.deliveryTelephonePrefixCountryCode.name}
                prefixName={formMeta.fields.deliveryTelephonePrefix.name}
                telephoneLabel={formMeta.fields.deliveryTelephone.label}
                telephoneName={formMeta.fields.deliveryTelephone.name}
                telephoneOnChange={(event) =>
                    updateContactInformation({
                        deliveryTelephone: event.currentTarget.value,
                    })
                }
                onPrefixChange={(dialCode, countryCode) =>
                    updateContactInformation({
                        deliveryTelephonePrefix: dialCode,
                        deliveryTelephonePrefixCountryCode: countryCode,
                    })
                }
            />

            {!pickupPlace && (
                <>
                    <TextInputControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.deliveryCompanyName.name}
                        textInputProps={{
                            label: formMeta.fields.deliveryCompanyName.label,
                            type: 'text',
                            autoComplete: 'organization',
                            onChange: (event) =>
                                updateContactInformation({
                                    deliveryCompanyName: event.currentTarget.value,
                                }),
                        }}
                    />
                    <TextInputControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.deliveryStreet.name}
                        textInputProps={{
                            label: formMeta.fields.deliveryStreet.label,
                            required: true,
                            type: 'text',
                            autoComplete: 'street-address',
                            onChange: (event) =>
                                updateContactInformation({
                                    deliveryStreet: event.currentTarget.value,
                                }),
                        }}
                    />

                    <FormColumn>
                        <TextInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.deliveryCity.name}
                            width="wide"
                            textInputProps={{
                                label: formMeta.fields.deliveryCity.label,
                                required: true,
                                type: 'text',
                                autoComplete: 'address-level2',
                                onChange: (event) =>
                                    updateContactInformation({
                                        deliveryCity: event.currentTarget.value,
                                    }),
                            }}
                        />

                        <TextInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.deliveryPostcode.name}
                            width="narrow"
                            textInputProps={{
                                label: formMeta.fields.deliveryPostcode.label,
                                required: true,
                                type: 'text',
                                autoComplete: 'postal-code',
                                inputMode: 'numeric',
                                onChange: (event) =>
                                    updateContactInformation({
                                        deliveryPostcode: event.currentTarget.value,
                                    }),
                            }}
                        />
                    </FormColumn>

                    <CountrySelectControlled
                        formName={formMeta.formName}
                        formProviderMethods={formProviderMethods}
                        label={formMeta.fields.deliveryCountry.label}
                        name={formMeta.fields.deliveryCountry.name}
                        onCountryChange={(country) => updateContactInformation({ deliveryCountry: country })}
                    />
                </>
            )}
        </div>
    );
};
