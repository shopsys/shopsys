import { ContactInformationDeliveryAddressStyled } from './ContactInformationDeliveryAddress.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine/ChoiceFormLine';
import { FormColumn } from 'components/Forms/Lib/FormColumn/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useCountriesQueryApi } from 'graphql/generated';
import { mapCountriesToSelectOptions } from 'helpers/mappers/country';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { CSSTransition } from 'react-transition-group';
import { useShopsysDispatch } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';
import { SelectOptionType } from 'types/selectOptions';

export const ContactInformationDeliveryAddress: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const { pickupPlace } = useCurrentCart();
    const { isUserLoggedIn, user } = useCurrentUserData();
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const { setValue, getValues } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [differentDeliveryAddressValue, deliveryAddressUuidValue] = useWatch({
        name: [formMeta.fields.differentDeliveryAddress.name, formMeta.fields.deliveryAddressUuid.name],
        control: formProviderMethods.control,
    });

    const isCustomAddressSelected = deliveryAddressUuidValue === '';
    const showAddressSelection = isUserLoggedIn && !pickupPlace && (!user || user.deliveryAddresses.length > 0);

    const [{ data: countriesData }] = useQueryError(useCountriesQueryApi());
    const countriesAsSelectOptions = useMemo(
        () => mapCountriesToSelectOptions(countriesData?.countries),
        [countriesData?.countries],
    );

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
                dispatch(
                    contactInformationActions.setDeliveryAddressFromPickupPlace({
                        ...pickupPlace,
                        country: selectedCountryOption,
                    }),
                );
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
        dispatch,
    ]);

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

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
                    <FormLine lg="65%">
                        <ChoiceFormLine>{checkbox}</ChoiceFormLine>
                    </FormLine>
                )}
                checkboxProps={{
                    label: formMeta.fields.differentDeliveryAddress.label,
                }}
            />
            <ContactInformationDeliveryAddressStyled contentElementHeight={contentElementHeight}>
                <CSSTransition
                    in={differentDeliveryAddressValue}
                    timeout={500}
                    classNames="contactInformationDeliveryAddress"
                    onEnter={calcHeight}
                    onExit={calcHeight}
                    unmountOnExit
                    nodeRef={cssTransitionRef}
                >
                    <div ref={cssTransitionRef}>
                        <div ref={contentElement}>
                            {showAddressSelection && (
                                <FormLine bottomGap lg="65%">
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
                                                                {deliveryAddress.firstName} {deliveryAddress.lastName}
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
                                    <FormColumn lg="65%">
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            name={formMeta.fields.deliveryFirstName.name}
                                            render={(textInput) => (
                                                <FormLine bottomGap width="100%" lg="50%">
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
                                                    dispatch(
                                                        contactInformationActions.setDeliveryFirstName(
                                                            event.currentTarget.value,
                                                        ),
                                                    );
                                                },
                                            }}
                                        />
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            name={formMeta.fields.deliveryLastName.name}
                                            render={(textInput) => (
                                                <FormLine bottomGap width="100%" lg="50%">
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
                                                    dispatch(
                                                        contactInformationActions.setDeliveryLastName(
                                                            event.currentTarget.value,
                                                        ),
                                                    ),
                                            }}
                                        />
                                    </FormColumn>
                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        name={formMeta.fields.deliveryCompanyName.name}
                                        render={(textInput) => (
                                            <FormLine bottomGap lg="65%">
                                                {textInput}
                                            </FormLine>
                                        )}
                                        formName={formMeta.formName}
                                        textInputProps={{
                                            label: formMeta.fields.deliveryCompanyName.label,
                                            required: false,
                                            type: 'text',
                                            autoComplete: 'organization',
                                            onBlur: (event) =>
                                                dispatch(
                                                    contactInformationActions.setDeliveryCompanyName(
                                                        event.currentTarget.value,
                                                    ),
                                                ),
                                        }}
                                    />
                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        name={formMeta.fields.deliveryTelephone.name}
                                        render={(textInput) => (
                                            <FormLine bottomGap lg="65%">
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
                                                dispatch(
                                                    contactInformationActions.setDeliveryTelephone(
                                                        event.currentTarget.value,
                                                    ),
                                                ),
                                        }}
                                    />
                                    {!pickupPlace && (
                                        <>
                                            <TextInputControlled
                                                control={formProviderMethods.control}
                                                name={formMeta.fields.deliveryStreet.name}
                                                render={(textInput) => (
                                                    <FormLine bottomGap lg="65%">
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
                                                        dispatch(
                                                            contactInformationActions.setDeliveryStreet(
                                                                event.currentTarget.value,
                                                            ),
                                                        ),
                                                }}
                                            />
                                            <FormColumn lg="65%">
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
                                                            dispatch(
                                                                contactInformationActions.setDeliveryCity(
                                                                    event.currentTarget.value,
                                                                ),
                                                            ),
                                                    }}
                                                />
                                                <TextInputControlled
                                                    control={formProviderMethods.control}
                                                    name={formMeta.fields.deliveryPostcode.name}
                                                    render={(textInput) => (
                                                        <FormLine bottomGap width="100%" lg="142px">
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
                                                            dispatch(
                                                                contactInformationActions.setDeliveryPostcode(
                                                                    event.currentTarget.value,
                                                                ),
                                                            ),
                                                    }}
                                                />
                                            </FormColumn>
                                            <FormLine lg="65%">
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
                                                                    dispatch(
                                                                        contactInformationActions.setDeliveryCountry(
                                                                            data[0] as SelectOptionType,
                                                                        ),
                                                                    );
                                                                }}
                                                                value={countriesAsSelectOptions.find(
                                                                    (option) => option.value === field.value.value,
                                                                )}
                                                            />

                                                            <FormLineError
                                                                error={error}
                                                                inputType="select"
                                                                testIdentifier={
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
                </CSSTransition>
                {!!pickupPlace && (
                    <div>
                        <strong>{t('Pickup place')}:</strong> {pickupPlace.street}, {pickupPlace.postcode}{' '}
                        {pickupPlace.city}, {pickupPlace.country.name}
                    </div>
                )}
            </ContactInformationDeliveryAddressStyled>
        </>
    );
};
