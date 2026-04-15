import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { Controller, useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { SelectOptionType } from 'types/selectOptions';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ContactInformationDeliveryAddressForm = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta();
    const countriesAsSelectOptions = useCountriesAsSelectOptions();
    const { pickupPlace } = useCurrentCart();

    return (
        <div className="flex flex-col gap-5">
            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    gridClassName="col-span-2"
                    name={formMeta.fields.deliveryFirstName.name}
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
                    gridClassName="col-span-2"
                    name={formMeta.fields.deliveryLastName.name}
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
                    gridClassName="col-span-2"
                    name={formMeta.fields.deliveryTelephone.name}
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
                            gridClassName="col-span-3"
                            name={formMeta.fields.deliveryCity.name}
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
                            gridClassName="col-start-4"
                            name={formMeta.fields.deliveryPostcode.name}
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

                    <FormColumn>
                        <FormLine className="col-span-3">
                            <Controller
                                name={formMeta.fields.deliveryCountry.name}
                                render={({ fieldState: { error }, field }) => (
                                    <>
                                        <Select
                                            isRequired
                                            ariaLabel={t('Select country', { ns: 'accessibility' })}
                                            label={formMeta.fields.deliveryCountry.label}
                                            tid={`${formMeta.formName}-${formMeta.fields.deliveryCountry.name}`}
                                            activeOption={countriesAsSelectOptions.find(
                                                (option) => option.value === field.value.value,
                                            )}
                                            options={countriesAsSelectOptions.map((option) => ({
                                                ...option,
                                                id: `${option.value}-my-id`,
                                            }))}
                                            onSelectOption={(...selectOnChangeEventData) => {
                                                field.onChange(...selectOnChangeEventData);
                                                updateContactInformation({
                                                    deliveryCountry: selectOnChangeEventData[0] as SelectOptionType,
                                                });
                                            }}
                                        />
                                        <FormLineError error={error} inputType="select" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormColumn>
                </>
            )}
        </div>
    );
};
