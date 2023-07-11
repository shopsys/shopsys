import { Heading } from 'components/Basic/Heading/Heading';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useCountriesQueryApi } from 'graphql/generated';
import { mapCountriesToSelectOptions } from 'helpers/mappers/country';

import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCalcElementHeight } from 'hooks/ui/useCalcElementHeight';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useEffect, useMemo, useRef } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { Transition } from 'react-transition-group';
import { ContactInformation } from 'store/zustand/slices/createContactInformationSlice';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { SelectOptionType } from 'types/selectOptions';

export const ContactInformationDeliveryAddress: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const t = useTypedTranslationFunction();
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const [elementHeight, calcHeight] = useCalcElementHeight(contentElement);
    const { pickupPlace } = useCurrentCart();
    const { isUserLoggedIn, user } = useCurrentUserData();
    const formProviderMethods = useFormContext<ContactInformation>();
    const { setValue, getValues } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [differentDeliveryAddressValue, deliveryAddressUuidValue] = useWatch({
        name: [formMeta.fields.differentDeliveryAddress.name, formMeta.fields.deliveryAddressUuid.name],
        control: formProviderMethods.control,
    });

    const isCustomAddressSelected = deliveryAddressUuidValue === '';
    const showAddressSelection = isUserLoggedIn && !pickupPlace && (!user || user.deliveryAddresses.length > 0);

    const [{ data: countriesData }] = useCountriesQueryApi();
    const countriesAsSelectOptions = useMemo(
        () => mapCountriesToSelectOptions(countriesData?.countries),
        [countriesData?.countries],
    );

    const transitionStyles = {
        entering: { height: elementHeight },
        entered: { height: elementHeight },
        exiting: { height: 0 },
        exited: { height: 0 },
        unmounted: {},
    };

    useEffect(() => {
        if (differentDeliveryAddressValue === true) {
            const selectedCountryOption = countriesAsSelectOptions.find((option) => {
                return option.value === pickupPlace?.country.code;
            });
            if (selectedCountryOption !== undefined && pickupPlace !== null) {
                const formValues = getValues();
                setValue(formMeta.fields.deliveryFirstName.name, formValues.firstName);
                setValue(formMeta.fields.deliveryLastName.name, formValues.lastName);
                setValue(formMeta.fields.deliveryCompanyName.name, formValues.companyName);
                setValue(formMeta.fields.deliveryTelephone.name, formValues.telephone);
                setValue(formMeta.fields.deliveryStreet.name, pickupPlace.street);
                setValue(formMeta.fields.deliveryCity.name, pickupPlace.city);
                setValue(formMeta.fields.deliveryPostcode.name, pickupPlace.postcode);
                setValue(formMeta.fields.deliveryCountry.name, selectedCountryOption);
                updateContactInformation({ ...pickupPlace, country: selectedCountryOption });
            }
        }
    }, [
        pickupPlace,
        differentDeliveryAddressValue,
        countriesAsSelectOptions,
        getValues,
        setValue,
        formMeta.fields.deliveryFirstName.name,
        formMeta.fields.deliveryLastName.name,
        formMeta.fields.deliveryCompanyName.name,
        formMeta.fields.deliveryTelephone.name,
        formMeta.fields.deliveryStreet.name,
        formMeta.fields.deliveryCity.name,
        formMeta.fields.deliveryPostcode.name,
        formMeta.fields.deliveryCountry.name,
        updateContactInformation,
    ]);

    useEffect(() => {
        if (isUserLoggedIn) {
            const deliveryAddress = user?.deliveryAddresses.find(
                (address) => address.uuid === deliveryAddressUuidValue,
            );
            const selectedCountryOption =
                countriesAsSelectOptions.find((option) => option.value === deliveryAddress?.country) ??
                countriesAsSelectOptions.find((option) => option.value === user?.country.code);

            if (selectedCountryOption !== undefined || countriesAsSelectOptions.length > 0) {
                setValue(formMeta.fields.deliveryFirstName.name, deliveryAddress?.firstName ?? '');
                setValue(formMeta.fields.deliveryLastName.name, deliveryAddress?.lastName ?? '');
                setValue(formMeta.fields.deliveryCompanyName.name, deliveryAddress?.companyName ?? '');
                setValue(formMeta.fields.deliveryTelephone.name, deliveryAddress?.telephone ?? '');
                if (pickupPlace === null) {
                    setValue(formMeta.fields.deliveryStreet.name, deliveryAddress?.street ?? '');
                    setValue(formMeta.fields.deliveryCity.name, deliveryAddress?.city ?? '');
                    setValue(formMeta.fields.deliveryPostcode.name, deliveryAddress?.postcode ?? '');
                    setValue(
                        formMeta.fields.deliveryCountry.name,
                        selectedCountryOption ?? countriesAsSelectOptions[0],
                    );
                }
            }
        }
    }, [
        countriesAsSelectOptions,
        deliveryAddressUuidValue,
        formMeta.fields.deliveryCity.name,
        formMeta.fields.deliveryCompanyName.name,
        formMeta.fields.deliveryCountry.name,
        formMeta.fields.deliveryFirstName.name,
        formMeta.fields.deliveryLastName.name,
        formMeta.fields.deliveryPostcode.name,
        formMeta.fields.deliveryStreet.name,
        formMeta.fields.deliveryTelephone.name,
        isUserLoggedIn,
        pickupPlace,
        setValue,
        user?.country.code,
        user?.deliveryAddresses,
    ]);

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
                <Transition
                    in={differentDeliveryAddressValue}
                    nodeRef={cssTransitionRef}
                    timeout={300}
                    onEnter={calcHeight}
                    onExit={calcHeight}
                    unmountOnExit
                >
                    {(state) => (
                        <div
                            className="overflow-hidden transition-all"
                            ref={cssTransitionRef}
                            style={{
                                ...transitionStyles[state],
                            }}
                        >
                            <div ref={contentElement}>
                                {showAddressSelection && (
                                    <FormLine bottomGap className="flex-none lg:w-[65%]">
                                        <div className="flex w-full flex-col">
                                            <RadiobuttonGroup
                                                name={formMeta.fields.deliveryAddressUuid.name}
                                                control={formProviderMethods.control}
                                                formName={formMeta.formName}
                                                radiobuttons={[
                                                    ...(user?.deliveryAddresses.map((deliveryAddress) => ({
                                                        label: (
                                                            <p>
                                                                <strong className="mr-1">
                                                                    {deliveryAddress.firstName}{' '}
                                                                    {deliveryAddress.lastName}
                                                                </strong>
                                                                {deliveryAddress.companyName}
                                                                <br />
                                                                {deliveryAddress.street}, {deliveryAddress.city},{' '}
                                                                {deliveryAddress.postcode}, {deliveryAddress.country}
                                                            </p>
                                                        ),
                                                        value: deliveryAddress.uuid,
                                                    })) ?? []),
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
                                                        className="relative mt-4 flex w-full flex-wrap rounded-xl border-2 border-border p-5"
                                                        key={key}
                                                    >
                                                        {radiobutton}
                                                    </div>
                                                )}
                                            />
                                        </div>
                                    </FormLine>
                                )}
                                {(!showAddressSelection || isCustomAddressSelected) && (
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
                                                        render={(textInput) => (
                                                            <FormLine bottomGap>{textInput}</FormLine>
                                                        )}
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
                                                            <FormLine
                                                                bottomGap
                                                                className="w-full flex-none lg:w-[142px]"
                                                            >
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
                                                                            deliveryCountry:
                                                                                data[0] as SelectOptionType,
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
                        </div>
                    )}
                </Transition>
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
