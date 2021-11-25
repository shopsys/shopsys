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
import { CSSTransition } from 'react-transition-group';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { getCountriesAsSelectOptions } from 'connectors/country/Country';
import Select from 'components/Forms/Select';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationDeliveryAddress: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const { pickupPlace } = useShopsysSelector((state) => state.user);
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const formProviderMethods = useFormContext();
    const differentDeliveryAddressValue = useWatch({ name: 'differentDeliveryAddress' });
    const deliveryFirstNameValue = useWatch({ name: 'deliveryFirstName' });
    const deliveryLastNameValue = useWatch({ name: 'deliveryLastName' });
    const deliveryCompanyNameValue = useWatch({ name: 'deliveryCompanyName' });
    const deliveryTelephoneValue = useWatch({ name: 'deliveryTelephone' });
    const deliveryStreetValue = useWatch({ name: 'deliveryStreet' });
    const deliveryCityValue = useWatch({ name: 'deliveryCity' });
    const deliveryPostcodeValue = useWatch({ name: 'deliveryPostcode' });
    const deliveryCountryValue = useWatch({ name: 'deliveryCountry' });
    const countrySelectOptions = getCountriesAsSelectOptions();
    useEffect(() => {
        const selectedCountryOption = countrySelectOptions.find((option) => {
            return option.value === pickupPlace?.country;
        });
        if (selectedCountryOption !== undefined && pickupPlace !== null) {
            formProviderMethods.setValue('deliveryStreet', pickupPlace.street);
            formProviderMethods.setValue('deliveryCity', pickupPlace.city);
            formProviderMethods.setValue('deliveryPostcode', pickupPlace.postcode);
            formProviderMethods.setValue('deliveryCountry', selectedCountryOption);
            dispatch(
                contactInformationActions.setDeliveryAddressFromPickupPlace({
                    ...pickupPlace,
                    country: selectedCountryOption,
                }),
            );
        }
    }, [pickupPlace, differentDeliveryAddressValue]);

    useEffect(() => {
        if (countrySelectOptions.length > 0 && differentDeliveryAddressValue === true && pickupPlace === null) {
            formProviderMethods.setValue('deliveryCountry', countrySelectOptions[0]);
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
                        name="differentDeliveryAddress"
                        render={({ field }) => (
                            <Checkbox
                                name={field.name}
                                fieldRef={field}
                                id="contactInformation_form-deliveryAddress"
                                label={t('Enter the delivery address')}
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
                                        name="deliveryFirstName"
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id="contactInformation_form-deliveryFirstName"
                                                    name="deliveryFirstName"
                                                    label={t('First name')}
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
                                        name="deliveryLastName"
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id="contactInformation_form-deliveryLastName"
                                                    name="deliveryLastName"
                                                    label={t('Last name')}
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
                                    name="deliveryCompanyName"
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id="contactInformation_form-deliveryCompanyName"
                                                name="deliveryCompanyName"
                                                label={t('Company')}
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
                                    name="deliveryTelephone"
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id="contactInformation_form-deliveryTelephone"
                                                name="deliveryTelephone"
                                                label={t('Telephone')}
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
                                    name="deliveryStreet"
                                    defaultValue={pickupPlace?.street}
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id="contactInformation_form-deliveryStreet"
                                                name="deliveryStreet"
                                                label={t('Street and house number')}
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
                                        name="deliveryCity"
                                        defaultValue={pickupPlace?.city}
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id="contactInformation_form-deliveryCity"
                                                    name="deliveryCity"
                                                    label={t('City')}
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
                                        name="deliveryPostcode"
                                        defaultValue={pickupPlace?.postcode}
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id="contactInformation_form-deliveryPostcode"
                                                    name="deliveryPostcode"
                                                    label={t('Postcode')}
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
                                    name="deliveryCountry"
                                    render={({ field }) => (
                                        <Select
                                            options={countrySelectOptions}
                                            onChange={field.onChange}
                                            isDisabled={pickupPlace !== null}
                                            value={deliveryCountryValue}
                                        />
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
