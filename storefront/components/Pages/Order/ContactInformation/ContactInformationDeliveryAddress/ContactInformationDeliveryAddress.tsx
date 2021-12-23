import {
    ContactInformationDeliveryAddressContentStyled,
    ContactInformationDeliveryAddressStyled,
} from './ContactInformationDeliveryAddress.style';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { FC, useEffect, useRef, useState } from 'react';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';
import { CSSTransition } from 'react-transition-group';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { getCountriesAsSelectOptions } from 'connectors/country/Country';
import Select from 'components/Forms/Select';
import TextInput from 'components/Forms/TextInput';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';

const ContactInformationDeliveryAddress: FC = () => {
    const dispatch = useShopsysDispatch();
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const { pickupPlace } = useShopsysSelector((state) => state.cart);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [
        differentDeliveryAddressValue,
        deliveryFirstNameValue,
        deliveryLastNameValue,
        deliveryCompanyNameValue,
        deliveryTelephoneValue,
        deliveryStreetValue,
        deliveryCityValue,
        deliveryPostcodeValue,
    ] = useWatch({
        name: [
            formMeta.fields.differentDeliveryAddress.name,
            formMeta.fields.deliveryFirstName.name,
            formMeta.fields.deliveryLastName.name,
            formMeta.fields.deliveryCompanyName.name,
            formMeta.fields.deliveryTelephone.name,
            formMeta.fields.deliveryStreet.name,
            formMeta.fields.deliveryCity.name,
            formMeta.fields.deliveryPostcode.name,
            formMeta.fields.deliveryCountry.name,
        ],
        control: formProviderMethods.control,
    });

    const countrySelectOptions = getCountriesAsSelectOptions();
    useEffect(() => {
        if (differentDeliveryAddressValue === true) {
            const selectedCountryOption = countrySelectOptions.find((option) => {
                return option.value === pickupPlace?.country.code;
            });
            if (selectedCountryOption !== undefined && pickupPlace !== null) {
                const formValues = formProviderMethods.getValues();
                formProviderMethods.setValue(formMeta.fields.deliveryFirstName.name, formValues.firstName);
                formProviderMethods.setValue(formMeta.fields.deliveryLastName.name, formValues.lastName);
                formProviderMethods.setValue(formMeta.fields.deliveryCompanyName.name, formValues.companyName);
                formProviderMethods.setValue(formMeta.fields.deliveryTelephone.name, formValues.telephone);
                formProviderMethods.setValue(formMeta.fields.deliveryStreet.name, pickupPlace.street);
                formProviderMethods.setValue(formMeta.fields.deliveryCity.name, pickupPlace.city);
                formProviderMethods.setValue(formMeta.fields.deliveryPostcode.name, pickupPlace.postcode);
                formProviderMethods.setValue(formMeta.fields.deliveryCountry.name, selectedCountryOption);
                dispatch(
                    contactInformationActions.setDeliveryAddressFromPickupPlace({
                        ...pickupPlace,
                        country: selectedCountryOption,
                    }),
                );
            }
            return;
        }
        setTimeout(() => {
            const firstCountrySelectOption = { ...countrySelectOptions[0] };

            formProviderMethods.setValue(formMeta.fields.deliveryFirstName.name, '');
            formProviderMethods.setValue(formMeta.fields.deliveryLastName.name, '');
            formProviderMethods.setValue(formMeta.fields.deliveryCompanyName.name, '');
            formProviderMethods.setValue(formMeta.fields.deliveryTelephone.name, '');
            formProviderMethods.setValue(formMeta.fields.deliveryStreet.name, '');
            formProviderMethods.setValue(formMeta.fields.deliveryCity.name, '');
            formProviderMethods.setValue(formMeta.fields.deliveryPostcode.name, '');
            formProviderMethods.setValue(formMeta.fields.deliveryCountry.name, firstCountrySelectOption);
            dispatch(
                contactInformationActions.setDeliveryAddressFromPickupPlace({
                    city: '',
                    postcode: '',
                    street: '',
                    country: firstCountrySelectOption,
                }),
            );
        }, 500);
    }, [pickupPlace, differentDeliveryAddressValue]);

    useEffect(() => {
        if (countrySelectOptions.length > 0 && differentDeliveryAddressValue === true && pickupPlace === null) {
            formProviderMethods.setValue(formMeta.fields.deliveryCountry.name, countrySelectOptions[0]);
        }
    }, [JSON.stringify(countrySelectOptions), differentDeliveryAddressValue]);

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    if (countrySelectOptions.length === 0) {
        return null;
    }

    return (
        <>
            <FormLine lg="65%">
                <ChoiceFormLine>
                    <Controller
                        name={formMeta.fields.differentDeliveryAddress.name}
                        render={({ field }) => (
                            <Checkbox
                                name={formMeta.fields.differentDeliveryAddress.name}
                                fieldRef={field}
                                id={formMeta.formName + '-' + formMeta.fields.differentDeliveryAddress.name}
                                label={formMeta.fields.differentDeliveryAddress.label}
                            />
                        )}
                    />
                </ChoiceFormLine>
            </FormLine>

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
                        <ContactInformationDeliveryAddressContentStyled ref={contentElement}>
                            <FormColumn lg="65%">
                                <FormLine bottomGap={true} width="100%" lg="50%">
                                    <Controller
                                        name={formMeta.fields.deliveryFirstName.name}
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id={
                                                        formMeta.formName + '-' + formMeta.fields.deliveryFirstName.name
                                                    }
                                                    name={formMeta.fields.deliveryFirstName.name}
                                                    label={formMeta.fields.deliveryFirstName.label}
                                                    required={true}
                                                    type="text"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                    onBlurCapture={() =>
                                                        dispatch(
                                                            contactInformationActions.setDeliveryFirstName(
                                                                deliveryFirstNameValue,
                                                            ),
                                                        )
                                                    }
                                                />
                                                <FormLineError error={error} inputType="text-input" />
                                            </>
                                        )}
                                    />
                                </FormLine>
                                <FormLine bottomGap={true} width="100%" lg="50%">
                                    <Controller
                                        name={formMeta.fields.deliveryLastName.name}
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id={formMeta.formName + '-' + formMeta.fields.deliveryLastName.name}
                                                    name={formMeta.fields.deliveryLastName.name}
                                                    label={formMeta.fields.deliveryLastName.label}
                                                    required={true}
                                                    type="text"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                    onBlurCapture={() =>
                                                        dispatch(
                                                            contactInformationActions.setDeliveryLastName(
                                                                deliveryLastNameValue,
                                                            ),
                                                        )
                                                    }
                                                />
                                                <FormLineError error={error} inputType="text-input" />
                                            </>
                                        )}
                                    />
                                </FormLine>
                            </FormColumn>
                            <FormLine bottomGap={true} lg="65%">
                                <Controller
                                    name={formMeta.fields.deliveryCompanyName.name}
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id={formMeta.formName + '-' + formMeta.fields.deliveryCompanyName.name}
                                                name={formMeta.fields.deliveryCompanyName.name}
                                                label={formMeta.fields.deliveryCompanyName.label}
                                                type="text"
                                                isTouched={isTouched}
                                                hasError={invalid}
                                                fieldRef={field}
                                                onBlurCapture={() =>
                                                    dispatch(
                                                        contactInformationActions.setDeliveryCompanyName(
                                                            deliveryCompanyNameValue,
                                                        ),
                                                    )
                                                }
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                            <FormLine bottomGap={true} lg="65%">
                                <Controller
                                    name={formMeta.fields.deliveryTelephone.name}
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id={formMeta.formName + '-' + formMeta.fields.deliveryTelephone.name}
                                                name={formMeta.fields.deliveryTelephone.name}
                                                label={formMeta.fields.deliveryTelephone.label}
                                                type="text"
                                                isTouched={isTouched}
                                                hasError={invalid}
                                                fieldRef={field}
                                                onBlurCapture={() =>
                                                    dispatch(
                                                        contactInformationActions.setDeliveryTelephone(
                                                            deliveryTelephoneValue,
                                                        ),
                                                    )
                                                }
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                            <FormLine bottomGap={true} lg="65%">
                                <Controller
                                    name={formMeta.fields.deliveryStreet.name}
                                    defaultValue={pickupPlace?.street}
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id={formMeta.formName + '-' + formMeta.fields.deliveryStreet.name}
                                                name={formMeta.fields.deliveryStreet.name}
                                                label={formMeta.fields.deliveryStreet.label}
                                                type="text"
                                                required={true}
                                                isTouched={isTouched}
                                                hasError={invalid}
                                                fieldRef={field}
                                                disabled={pickupPlace !== null}
                                                onBlurCapture={() =>
                                                    dispatch(
                                                        contactInformationActions.setDeliveryStreet(
                                                            deliveryStreetValue,
                                                        ),
                                                    )
                                                }
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                            <FormColumn lg="65%">
                                <FormLine bottomGap={true}>
                                    <Controller
                                        name={formMeta.fields.deliveryCity.name}
                                        defaultValue={pickupPlace?.city}
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id={formMeta.formName + '-' + formMeta.fields.deliveryCity.name}
                                                    name={formMeta.fields.deliveryCity.name}
                                                    label={formMeta.fields.deliveryCity.label}
                                                    required={true}
                                                    type="text"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                    disabled={pickupPlace !== null}
                                                    onBlurCapture={() =>
                                                        dispatch(
                                                            contactInformationActions.setDeliveryCity(
                                                                deliveryCityValue,
                                                            ),
                                                        )
                                                    }
                                                />
                                                <FormLineError error={error} inputType="text-input" />
                                            </>
                                        )}
                                    />
                                </FormLine>
                                <FormLine bottomGap={true} width="100%" lg="142px">
                                    <Controller
                                        name={formMeta.fields.deliveryPostcode.name}
                                        defaultValue={pickupPlace?.postcode}
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id={formMeta.formName + '-' + formMeta.fields.deliveryPostcode.name}
                                                    name={formMeta.fields.deliveryPostcode.name}
                                                    label={formMeta.fields.deliveryPostcode.label}
                                                    required={true}
                                                    type="text"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                    disabled={pickupPlace !== null}
                                                    onBlurCapture={() =>
                                                        dispatch(
                                                            contactInformationActions.setDeliveryPostcode(
                                                                deliveryPostcodeValue,
                                                            ),
                                                        )
                                                    }
                                                />
                                                <FormLineError error={error} inputType="text-input" />
                                            </>
                                        )}
                                    />
                                </FormLine>
                            </FormColumn>
                            <FormLine lg="65%">
                                <Controller
                                    name={formMeta.fields.deliveryCountry.name}
                                    render={({ fieldState: { invalid, error }, field }) => (
                                        <>
                                            <Select
                                                hasError={invalid}
                                                options={countrySelectOptions}
                                                onChange={field.onChange}
                                                isDisabled={pickupPlace !== null}
                                                value={countrySelectOptions.find(
                                                    (option) => option.value === field.value.value,
                                                )}
                                                fieldRef={field}
                                            />

                                            <FormLineError error={error} inputType="select" />
                                        </>
                                    )}
                                />
                            </FormLine>
                        </ContactInformationDeliveryAddressContentStyled>
                    </div>
                </CSSTransition>
            </ContactInformationDeliveryAddressStyled>
        </>
    );
};

/* @component */
export default ContactInformationDeliveryAddress;
