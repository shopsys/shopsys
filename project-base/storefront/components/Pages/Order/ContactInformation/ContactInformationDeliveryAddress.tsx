import { Heading } from 'components/Basic/Heading/Heading';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useCountriesQueryApi } from 'graphql/generated';
import { mapCountriesToSelectOptions } from 'helpers/mappers/country';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useMemo } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { SelectOptionType } from 'types/selectOptions';

export const ContactInformationDeliveryAddress: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const { pickupPlace } = useCurrentCart();
    const user = useCurrentCustomerData();
    const formProviderMethods = useFormContext<ContactInformation>();
    const { setValue, getValues } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [isDifferentDeliveryAddress, deliveryAddressUuid] = useWatch({
        name: [formMeta.fields.differentDeliveryAddress.name, formMeta.fields.deliveryAddressUuid.name],
        control: formProviderMethods.control,
    });

    const showAddressSelection = !!user?.deliveryAddresses.length && !pickupPlace;

    const [{ data: countriesData }] = useCountriesQueryApi();
    const countriesAsSelectOptions = useMemo(
        () => mapCountriesToSelectOptions(countriesData?.countries),
        [countriesData?.countries],
    );

    useEffect(() => {
        if (isDifferentDeliveryAddress && pickupPlace) {
            const selectedCountryOption = countriesAsSelectOptions.find(
                (option) => option.value === pickupPlace.country.code,
            );

            if (selectedCountryOption) {
                const formValues = getValues();

                setValue(formMeta.fields.deliveryFirstName.name, formValues.firstName, { shouldValidate: true });
                setValue(formMeta.fields.deliveryLastName.name, formValues.lastName, { shouldValidate: true });
                setValue(formMeta.fields.deliveryTelephone.name, formValues.telephone, { shouldValidate: true });
                setValue(formMeta.fields.deliveryStreet.name, pickupPlace.street, { shouldValidate: true });
                setValue(formMeta.fields.deliveryCity.name, pickupPlace.city, { shouldValidate: true });
                setValue(formMeta.fields.deliveryPostcode.name, pickupPlace.postcode, { shouldValidate: true });
                setValue(formMeta.fields.deliveryCountry.name, selectedCountryOption, { shouldValidate: true });

                updateContactInformation({ ...pickupPlace, country: selectedCountryOption });
            }
        }
    }, [countriesAsSelectOptions, isDifferentDeliveryAddress]);

    useEffect(() => {
        if (user && deliveryAddressUuid) {
            const deliveryAddress = user.deliveryAddresses.find((address) => address.uuid === deliveryAddressUuid)!;
            const selectedCountryOption = countriesAsSelectOptions.find(
                (option) => option.label === deliveryAddress.country,
            )!;

            if (countriesAsSelectOptions.length) {
                setValue(formMeta.fields.deliveryFirstName.name, deliveryAddress.firstName, { shouldValidate: true });
                setValue(formMeta.fields.deliveryLastName.name, deliveryAddress.lastName, { shouldValidate: true });
                setValue(formMeta.fields.deliveryCompanyName.name, deliveryAddress.companyName, {
                    shouldValidate: true,
                });
                setValue(formMeta.fields.deliveryTelephone.name, deliveryAddress.telephone, { shouldValidate: true });
                setValue(formMeta.fields.deliveryCountry.name, selectedCountryOption, { shouldValidate: true });

                if (!pickupPlace) {
                    setValue(formMeta.fields.deliveryStreet.name, deliveryAddress.street, { shouldValidate: true });
                    setValue(formMeta.fields.deliveryCity.name, deliveryAddress.city, { shouldValidate: true });
                    setValue(formMeta.fields.deliveryPostcode.name, deliveryAddress.postcode, { shouldValidate: true });
                }
            }
        } else if (deliveryAddressUuid === '') {
            setValue(formMeta.fields.deliveryFirstName.name, '');
            setValue(formMeta.fields.deliveryLastName.name, '');
            setValue(formMeta.fields.deliveryCompanyName.name, '');
            setValue(formMeta.fields.deliveryTelephone.name, '');
            setValue(formMeta.fields.deliveryStreet.name, '');
            setValue(formMeta.fields.deliveryCity.name, '');
            setValue(formMeta.fields.deliveryPostcode.name, '');
            setValue(formMeta.fields.deliveryCountry.name, countriesAsSelectOptions[0], { shouldValidate: true });
        }
    }, [deliveryAddressUuid, countriesAsSelectOptions]);

    if (countriesAsSelectOptions.length === 0) {
        return null;
    }

    return (
        <>
            <Heading type="h3">{t('Delivery address')}</Heading>

            <CheckboxControlled
                name={formMeta.fields.differentDeliveryAddress.name}
                control={formProviderMethods.control}
                formName={formMeta.formName}
                render={(checkbox) => (
                    <FormLine className="flex-none lg:w-[65%]">
                        <ChoiceFormLine>{checkbox}</ChoiceFormLine>
                    </FormLine>
                )}
                checkboxProps={{
                    label: formMeta.fields.differentDeliveryAddress.label,
                }}
            />

            <div className="pb-10">
                {isDifferentDeliveryAddress && (
                    <div>
                        {showAddressSelection && (
                            <FormLine bottomGap className="flex-none lg:w-[65%]">
                                <div className="flex w-full flex-col">
                                    <RadiobuttonGroup
                                        name={formMeta.fields.deliveryAddressUuid.name}
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
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
                                            },
                                        ]}
                                        render={(radiobutton, key) => (
                                            <div
                                                className="relative mt-4 flex w-full flex-wrap rounded border-2 border-border p-5"
                                                key={key}
                                            >
                                                {radiobutton}
                                            </div>
                                        )}
                                    />
                                </div>
                            </FormLine>
                        )}

                        {(user?.deliveryAddresses.length
                            ? deliveryAddressUuid || deliveryAddressUuid === ''
                            : true) && (
                            <>
                                <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        name={formMeta.fields.deliveryFirstName.name}
                                        render={(textInput) => (
                                            <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                                {textInput}
                                            </FormLine>
                                        )}
                                        formName={formMeta.formName}
                                        textInputProps={{
                                            label: formMeta.fields.deliveryFirstName.label,
                                            required: true,
                                            type: 'text',
                                            autoComplete: 'given-name',
                                            onBlur: (event) => {
                                                updateContactInformation({
                                                    deliveryFirstName: event.currentTarget.value,
                                                });
                                            },
                                        }}
                                    />

                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        name={formMeta.fields.deliveryLastName.name}
                                        render={(textInput) => (
                                            <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                                {textInput}
                                            </FormLine>
                                        )}
                                        formName={formMeta.formName}
                                        textInputProps={{
                                            label: formMeta.fields.deliveryLastName.label,
                                            required: true,
                                            type: 'text',
                                            autoComplete: 'family-name',
                                            onBlur: (event) =>
                                                updateContactInformation({
                                                    deliveryLastName: event.currentTarget.value,
                                                }),
                                        }}
                                    />
                                </FormColumn>

                                {!pickupPlace && (
                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        name={formMeta.fields.deliveryCompanyName.name}
                                        render={(textInput) => (
                                            <FormLine bottomGap className="flex-none lg:w-[65%]">
                                                {textInput}
                                            </FormLine>
                                        )}
                                        formName={formMeta.formName}
                                        textInputProps={{
                                            label: formMeta.fields.deliveryCompanyName.label,
                                            type: 'text',
                                            autoComplete: 'organization',
                                            onBlur: (event) =>
                                                updateContactInformation({
                                                    deliveryCompanyName: event.currentTarget.value,
                                                }),
                                        }}
                                    />
                                )}

                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    name={formMeta.fields.deliveryTelephone.name}
                                    render={(textInput) => (
                                        <FormLine bottomGap className="flex-none lg:w-[65%]">
                                            {textInput}
                                        </FormLine>
                                    )}
                                    formName={formMeta.formName}
                                    textInputProps={{
                                        label: formMeta.fields.deliveryTelephone.label,
                                        required: true,
                                        type: 'tel',
                                        autoComplete: 'tel',
                                        onBlur: (event) =>
                                            updateContactInformation({
                                                deliveryTelephone: event.currentTarget.value,
                                            }),
                                    }}
                                />

                                {!pickupPlace && (
                                    <>
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            name={formMeta.fields.deliveryStreet.name}
                                            render={(textInput) => (
                                                <FormLine bottomGap className="flex-none lg:w-[65%]">
                                                    {textInput}
                                                </FormLine>
                                            )}
                                            formName={formMeta.formName}
                                            textInputProps={{
                                                label: formMeta.fields.deliveryStreet.label,
                                                required: true,
                                                type: 'text',
                                                autoComplete: 'street-address',
                                                onBlur: (event) =>
                                                    updateContactInformation({
                                                        deliveryStreet: event.currentTarget.value,
                                                    }),
                                            }}
                                        />

                                        <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                                            <TextInputControlled
                                                control={formProviderMethods.control}
                                                name={formMeta.fields.deliveryCity.name}
                                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                                formName={formMeta.formName}
                                                textInputProps={{
                                                    label: formMeta.fields.deliveryCity.label,
                                                    required: true,
                                                    type: 'text',
                                                    autoComplete: 'address-level2',
                                                    onBlur: (event) =>
                                                        updateContactInformation({
                                                            deliveryCity: event.currentTarget.value,
                                                        }),
                                                }}
                                            />

                                            <TextInputControlled
                                                control={formProviderMethods.control}
                                                name={formMeta.fields.deliveryPostcode.name}
                                                render={(textInput) => (
                                                    <FormLine bottomGap className="w-full flex-none lg:w-[142px]">
                                                        {textInput}
                                                    </FormLine>
                                                )}
                                                formName={formMeta.formName}
                                                textInputProps={{
                                                    label: formMeta.fields.deliveryPostcode.label,
                                                    required: true,
                                                    type: 'text',
                                                    autoComplete: 'postal-code',
                                                    onBlur: (event) =>
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
                                                            label={formMeta.fields.deliveryCountry.label}
                                                            hasError={invalid}
                                                            options={countriesAsSelectOptions}
                                                            onChange={(...data) => {
                                                                field.onChange(...data);
                                                                updateContactInformation({
                                                                    deliveryCountry: data[0] as SelectOptionType,
                                                                });
                                                            }}
                                                            value={countriesAsSelectOptions.find(
                                                                (option) => option.value === field.value.value,
                                                            )}
                                                        />

                                                        <FormLineError
                                                            error={error}
                                                            inputType="select"
                                                            dataTestId={
                                                                formMeta.formName +
                                                                '-' +
                                                                formMeta.fields.deliveryCountry.name +
                                                                '-error'
                                                            }
                                                        />
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
