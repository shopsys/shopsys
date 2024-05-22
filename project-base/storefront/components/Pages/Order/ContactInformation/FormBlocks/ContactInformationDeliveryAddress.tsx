import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import useTranslation from 'next-translate/useTranslation';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { SelectOptionType } from 'types/selectOptions';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';

export const ContactInformationDeliveryAddress: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const { pickupPlace } = useCurrentCart();
    const user = useCurrentCustomerData();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [isDeliveryAddressDifferentFromBilling, deliveryAddressUuid] = useWatch({
        name: [formMeta.fields.isDeliveryAddressDifferentFromBilling.name, formMeta.fields.deliveryAddressUuid.name],
        control: formProviderMethods.control,
    });
    const showAddressSelection = !!user?.deliveryAddresses.length && !pickupPlace;
    const countriesAsSelectOptions = useCountriesAsSelectOptions();

    if (!countriesAsSelectOptions.length) {
        return null;
    }

    return (
        <>
            <div className="h4 mb-3">{t('Delivery address')}</div>

            <CheckboxControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.isDeliveryAddressDifferentFromBilling.name}
                checkboxProps={{
                    label: formMeta.fields.isDeliveryAddressDifferentFromBilling.label,
                }}
                render={(checkbox) => (
                    <FormLine className="flex-none lg:w-[65%]">
                        <ChoiceFormLine>{checkbox}</ChoiceFormLine>
                    </FormLine>
                )}
                onChange={(event) =>
                    updateContactInformation({ isDeliveryAddressDifferentFromBilling: event.target.checked })
                }
            />

            <div className="pb-10">
                {isDeliveryAddressDifferentFromBilling && (
                    <div>
                        {showAddressSelection && (
                            <FormLine bottomGap className="flex-none lg:w-[65%]">
                                <div className="flex w-full flex-col">
                                    <RadiobuttonGroup
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.deliveryAddressUuid.name}
                                        radiobuttons={[
                                            ...user.deliveryAddresses.map((deliveryAddress) => ({
                                                label: (
                                                    <p>
                                                        <strong className="mr-1">
                                                            {deliveryAddress.firstName} {deliveryAddress.lastName}
                                                        </strong>
                                                        {deliveryAddress.companyName}
                                                        <br />
                                                        {deliveryAddress.street}, {deliveryAddress.city},{' '}
                                                        {deliveryAddress.postcode}, {deliveryAddress.country}
                                                    </p>
                                                ),
                                                value: deliveryAddress.uuid,
                                            })),
                                            {
                                                label: (
                                                    <p>
                                                        <strong>{t('Different delivery address')}</strong>
                                                    </p>
                                                ),
                                                value: '',
                                                id: '-new-delivery-address',
                                            },
                                        ]}
                                        render={(radiobutton, key) => (
                                            <div
                                                key={key}
                                                className="relative mt-4 flex w-full flex-wrap rounded border-2 border-skyBlue p-5"
                                            >
                                                {radiobutton}
                                            </div>
                                        )}
                                        onChange={(event) =>
                                            updateContactInformation({ deliveryAddressUuid: event.currentTarget.value })
                                        }
                                    />
                                </div>
                            </FormLine>
                        )}
                        {(!user?.deliveryAddresses.length || deliveryAddressUuid === '' || !!pickupPlace) && (
                            <>
                                <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.deliveryFirstName.name}
                                        render={(textInput) => (
                                            <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                                {textInput}
                                            </FormLine>
                                        )}
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
                                        render={(textInput) => (
                                            <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                                {textInput}
                                            </FormLine>
                                        )}
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

                                {!pickupPlace && (
                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.deliveryCompanyName.name}
                                        render={(textInput) => (
                                            <FormLine bottomGap className="flex-none lg:w-[65%]">
                                                {textInput}
                                            </FormLine>
                                        )}
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
                                )}

                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.deliveryTelephone.name}
                                    render={(textInput) => (
                                        <FormLine bottomGap className="flex-none lg:w-[65%]">
                                            {textInput}
                                        </FormLine>
                                    )}
                                    textInputProps={{
                                        label: formMeta.fields.deliveryTelephone.label,
                                        required: true,
                                        type: 'tel',
                                        autoComplete: 'tel',
                                        onChange: (event) =>
                                            updateContactInformation({
                                                deliveryTelephone: event.currentTarget.value,
                                            }),
                                    }}
                                />

                                {!pickupPlace && (
                                    <>
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            formName={formMeta.formName}
                                            name={formMeta.fields.deliveryStreet.name}
                                            render={(textInput) => (
                                                <FormLine bottomGap className="flex-none lg:w-[65%]">
                                                    {textInput}
                                                </FormLine>
                                            )}
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

                                        <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                                            <TextInputControlled
                                                control={formProviderMethods.control}
                                                formName={formMeta.formName}
                                                name={formMeta.fields.deliveryCity.name}
                                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
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
                                                render={(textInput) => (
                                                    <FormLine bottomGap className="w-full flex-none lg:w-[142px]">
                                                        {textInput}
                                                    </FormLine>
                                                )}
                                                textInputProps={{
                                                    label: formMeta.fields.deliveryPostcode.label,
                                                    required: true,
                                                    type: 'text',
                                                    autoComplete: 'postal-code',
                                                    onChange: (event) =>
                                                        updateContactInformation({
                                                            deliveryPostcode: event.currentTarget.value,
                                                        }),
                                                }}
                                            />
                                        </FormColumn>

                                        <FormLine className="flex-none lg:w-[65%]">
                                            <Controller
                                                name={formMeta.fields.deliveryCountry.name}
                                                render={({ fieldState: { invalid, error }, field }) => (
                                                    <>
                                                        <Select
                                                            hasError={invalid}
                                                            id={formMeta.fields.deliveryCountry.name + '-select'}
                                                            label={formMeta.fields.deliveryCountry.label}
                                                            options={countriesAsSelectOptions.map((option) => ({
                                                                ...option,
                                                                id: option.value + '-my-id',
                                                            }))}
                                                            value={countriesAsSelectOptions.find(
                                                                (option) => option.value === field.value.value,
                                                            )}
                                                            onChange={(...data) => {
                                                                field.onChange(...data);
                                                                updateContactInformation({
                                                                    deliveryCountry: data[0] as SelectOptionType,
                                                                });
                                                            }}
                                                        />

                                                        <FormLineError error={error} inputType="select" />
                                                    </>
                                                )}
                                            />
                                        </FormLine>
                                    </>
                                )}
                            </>
                        )}
                    </div>
                )}

                {!!pickupPlace && (
                    <div>
                        <strong>{t('Pickup place')}:</strong> {pickupPlace.street}, {pickupPlace.postcode}{' '}
                        {pickupPlace.city}, {pickupPlace.country.name}
                    </div>
                )}
            </div>
        </>
    );
};
