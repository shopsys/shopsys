import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { AnimatePresence } from 'framer-motion';
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
    const isNewDeliveryAddressSelected = deliveryAddressUuid === 'new-delivery-address';

    if (!countriesAsSelectOptions.length) {
        return null;
    }

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Delivery address')}</FormHeading>

            <CheckboxControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.isDeliveryAddressDifferentFromBilling.name}
                render={(checkbox) => <FormLine>{checkbox}</FormLine>}
                checkboxProps={{
                    label: formMeta.fields.isDeliveryAddressDifferentFromBilling.label,
                }}
                onChange={(event) =>
                    updateContactInformation({ isDeliveryAddressDifferentFromBilling: event.target.checked })
                }
            />

            <AnimatePresence initial={false}>
                {isDeliveryAddressDifferentFromBilling && (
                    <AnimateCollapseDiv className="!block" keyName="different-delivery-address">
                        {showAddressSelection && (
                            <div className="flex w-full flex-col">
                                <RadiobuttonGroup
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.deliveryAddressUuid.name}
                                    radiobuttons={[
                                        ...user.deliveryAddresses.map((deliveryAddress) => ({
                                            label: (
                                                <p className="flex flex-col">
                                                    <strong className="mr-1">
                                                        {deliveryAddress.firstName} {deliveryAddress.lastName}
                                                    </strong>
                                                    <span>{deliveryAddress.companyName}</span>
                                                    <span>{deliveryAddress.telephone}</span>
                                                    <span>
                                                        {deliveryAddress.street}, {deliveryAddress.city},{' '}
                                                        {deliveryAddress.postcode}
                                                    </span>
                                                    <span>{deliveryAddress.country.name}</span>
                                                </p>
                                            ),
                                            value: deliveryAddress.uuid,
                                            labelWrapperClassName: 'flex-row-reverse',
                                        })),
                                        {
                                            label: (
                                                <p>
                                                    <span className="font-bold">{t('Different delivery address')}</span>
                                                </p>
                                            ),
                                            value: 'new-delivery-address',
                                            id: '-new-delivery-address',
                                            labelWrapperClassName: 'flex-row-reverse',
                                        },
                                    ]}
                                    render={(radiobutton, key) => (
                                        <div
                                            key={key}
                                            className="relative mt-4 flex w-full flex-wrap rounded border-2 border-borderAccent bg-background p-5"
                                        >
                                            {radiobutton}
                                        </div>
                                    )}
                                    onChange={(event) =>
                                        updateContactInformation({ deliveryAddressUuid: event.currentTarget.value })
                                    }
                                />
                            </div>
                        )}
                        <AnimatePresence initial={false}>
                            {(!user?.deliveryAddresses.length || isNewDeliveryAddressSelected || !!pickupPlace) && (
                                <AnimateCollapseDiv className="!block" keyName="different-address">
                                    <FormColumn className="mt-4">
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            formName={formMeta.formName}
                                            name={formMeta.fields.deliveryFirstName.name}
                                            render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
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
                                            render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
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
                                            render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
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
                                        render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
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
                                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
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
                                                        <FormLine bottomGap isSmallInput>
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

                                            <FormLine>
                                                <Controller
                                                    name={formMeta.fields.deliveryCountry.name}
                                                    render={({ fieldState: { invalid, error }, field }) => (
                                                        <>
                                                            <Select
                                                                hasError={invalid}
                                                                label={formMeta.fields.deliveryCountry.label}
                                                                id={
                                                                    formMeta.formName +
                                                                    '-' +
                                                                    formMeta.fields.deliveryCountry.name
                                                                }
                                                                options={countriesAsSelectOptions.map((option) => ({
                                                                    ...option,
                                                                    id: option.value + '-my-id',
                                                                }))}
                                                                value={countriesAsSelectOptions.find(
                                                                    (option) => option.value === field.value.value,
                                                                )}
                                                                onChange={(...selectOnChangeEventData) => {
                                                                    field.onChange(...selectOnChangeEventData);
                                                                    updateContactInformation({
                                                                        deliveryCountry:
                                                                            selectOnChangeEventData[0] as SelectOptionType,
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
                                </AnimateCollapseDiv>
                            )}
                        </AnimatePresence>
                    </AnimateCollapseDiv>
                )}

                {!!pickupPlace && (
                    <div className="mt-2">
                        <strong>{t('Pickup place')}:</strong> {pickupPlace.name}, {pickupPlace.street},{' '}
                        {pickupPlace.postcode} {pickupPlace.city}, {pickupPlace.country.name}
                    </div>
                )}
            </AnimatePresence>
        </FormBlockWrapper>
    );
};
