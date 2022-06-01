import {
    ContactInformationDeliveryAddressContentStyled,
    ContactInformationDeliveryAddressStyled,
    ListItemStyled,
    ListStyled,
} from './ContactInformationDeliveryAddress.style';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Radiobutton from 'components/Forms/Radiobutton';
import Select from 'components/Forms/Select';
import TextInput from 'components/Forms/TextInput';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useCountriesAsSelectOptions } from 'connectors/country/Country';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import React, { FC, useEffect, useRef, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { CSSTransition } from 'react-transition-group';
import { useShopsysDispatch } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';

const ContactInformationDeliveryAddress: FC = () => {
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

    const countrySelectOptions = useCountriesAsSelectOptions();
    useEffect(() => {
        if (differentDeliveryAddressValue === true) {
            const selectedCountryOption = countrySelectOptions.find((option) => {
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
        countrySelectOptions,
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

    useEffect(() => {
        if (countrySelectOptions.length > 0 && differentDeliveryAddressValue === true && pickupPlace === null) {
            setValue(formMeta.fields.deliveryCountry.name, countrySelectOptions[0]);
        }
    }, [
        countrySelectOptions,
        differentDeliveryAddressValue,
        formMeta.fields.deliveryCountry.name,
        pickupPlace,
        setValue,
    ]);

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    useEffect(() => {
        const deliveryAddress = user?.deliveryAddresses.find((address) => address.uuid === deliveryAddressUuidValue);
        const selectedCountryOption = countrySelectOptions.find((option) => {
            return option.value === deliveryAddress?.country;
        });

        if (selectedCountryOption !== undefined || countrySelectOptions.length > 0) {
            setValue(formMeta.fields.deliveryFirstName.name, deliveryAddress?.firstName ?? '');
            setValue(formMeta.fields.deliveryLastName.name, deliveryAddress?.lastName ?? '');
            setValue(formMeta.fields.deliveryCompanyName.name, deliveryAddress?.companyName ?? '');
            setValue(formMeta.fields.deliveryTelephone.name, deliveryAddress?.telephone ?? '');
            if (pickupPlace === null) {
                setValue(formMeta.fields.deliveryStreet.name, deliveryAddress?.street ?? '');
                setValue(formMeta.fields.deliveryCity.name, deliveryAddress?.city ?? '');
                setValue(formMeta.fields.deliveryPostcode.name, deliveryAddress?.postcode ?? '');
                setValue(formMeta.fields.deliveryCountry.name, selectedCountryOption ?? countrySelectOptions[0]);
            }
        }
    }, [
        countrySelectOptions,
        deliveryAddressUuidValue,
        formMeta.fields.deliveryCity.name,
        formMeta.fields.deliveryCompanyName.name,
        formMeta.fields.deliveryCountry.name,
        formMeta.fields.deliveryFirstName.name,
        formMeta.fields.deliveryLastName.name,
        formMeta.fields.deliveryPostcode.name,
        formMeta.fields.deliveryStreet.name,
        formMeta.fields.deliveryTelephone.name,
        pickupPlace,
        setValue,
        user?.deliveryAddresses,
    ]);

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
                            {isUserLoggedIn && (
                                <FormLine bottomGap={true} lg="65%">
                                    <ListStyled>
                                        {user?.deliveryAddresses.map((address) => (
                                            <Controller
                                                key={address.uuid}
                                                name={formMeta.fields.deliveryAddressUuid.name}
                                                render={({ field }) => (
                                                    <ListItemStyled>
                                                        <Radiobutton
                                                            id={
                                                                formMeta.formName +
                                                                '-' +
                                                                formMeta.fields.deliveryAddressUuid.name +
                                                                '-' +
                                                                address.uuid
                                                            }
                                                            label={
                                                                <p>
                                                                    <strong>
                                                                        {address.firstName} {address.lastName}
                                                                    </strong>
                                                                    {address.companyName}
                                                                    <br />
                                                                    {address.street}, {address.city}, {address.postcode}
                                                                    , {address.country}
                                                                </p>
                                                            }
                                                            name={formMeta.fields.deliveryAddressUuid.name}
                                                            fieldRef={field}
                                                            value={address.uuid}
                                                            checked={deliveryAddressUuidValue === address.uuid}
                                                        />
                                                    </ListItemStyled>
                                                )}
                                            />
                                        ))}
                                        <Controller
                                            name={formMeta.fields.deliveryAddressUuid.name}
                                            render={({ field }) => (
                                                <ListItemStyled>
                                                    <Radiobutton
                                                        id={
                                                            formMeta.formName +
                                                            '-' +
                                                            formMeta.fields.deliveryAddressUuid.name +
                                                            '-different'
                                                        }
                                                        label={
                                                            <p>
                                                                <strong>{t('Different delivery address')}</strong>
                                                            </p>
                                                        }
                                                        name={formMeta.fields.deliveryAddressUuid.name}
                                                        fieldRef={field}
                                                        value={''}
                                                        checked={deliveryAddressUuidValue === ''}
                                                    />
                                                </ListItemStyled>
                                            )}
                                        />
                                    </ListStyled>
                                </FormLine>
                            )}
                            {(!isUserLoggedIn || isCustomAddressSelected) && (
                                <>
                                    <FormColumn lg="65%">
                                        <FormLine bottomGap={true} width="100%" lg="50%">
                                            <Controller
                                                name={formMeta.fields.deliveryFirstName.name}
                                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                                    <>
                                                        <TextInput
                                                            id={
                                                                formMeta.formName +
                                                                '-' +
                                                                formMeta.fields.deliveryFirstName.name
                                                            }
                                                            name={formMeta.fields.deliveryFirstName.name}
                                                            label={formMeta.fields.deliveryFirstName.label}
                                                            required={true}
                                                            type="text"
                                                            isTouched={isTouched}
                                                            hasError={invalid}
                                                            fieldRef={field}
                                                        />
                                                        <FormLineError
                                                            error={error}
                                                            inputType="text-input"
                                                            data-testid={
                                                                formMeta.formName +
                                                                '-' +
                                                                formMeta.fields.deliveryFirstName.name +
                                                                '-error'
                                                            }
                                                        />
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
                                                            id={
                                                                formMeta.formName +
                                                                '-' +
                                                                formMeta.fields.deliveryLastName.name
                                                            }
                                                            name={formMeta.fields.deliveryLastName.name}
                                                            label={formMeta.fields.deliveryLastName.label}
                                                            required={true}
                                                            type="text"
                                                            isTouched={isTouched}
                                                            hasError={invalid}
                                                            fieldRef={field}
                                                        />
                                                        <FormLineError
                                                            error={error}
                                                            inputType="text-input"
                                                            data-testid={
                                                                formMeta.formName +
                                                                '-' +
                                                                formMeta.fields.deliveryLastName.name +
                                                                '-error'
                                                            }
                                                        />
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
                                                        id={
                                                            formMeta.formName +
                                                            '-' +
                                                            formMeta.fields.deliveryCompanyName.name
                                                        }
                                                        name={formMeta.fields.deliveryCompanyName.name}
                                                        label={formMeta.fields.deliveryCompanyName.label}
                                                        type="text"
                                                        isTouched={isTouched}
                                                        hasError={invalid}
                                                        fieldRef={field}
                                                    />
                                                    <FormLineError
                                                        error={error}
                                                        inputType="text-input"
                                                        data-testid={
                                                            formMeta.formName +
                                                            '-' +
                                                            formMeta.fields.deliveryCompanyName.name +
                                                            '-error'
                                                        }
                                                    />
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
                                                        id={
                                                            formMeta.formName +
                                                            '-' +
                                                            formMeta.fields.deliveryTelephone.name
                                                        }
                                                        name={formMeta.fields.deliveryTelephone.name}
                                                        label={formMeta.fields.deliveryTelephone.label}
                                                        type="text"
                                                        isTouched={isTouched}
                                                        hasError={invalid}
                                                        fieldRef={field}
                                                    />
                                                    <FormLineError
                                                        error={error}
                                                        inputType="text-input"
                                                        data-testid={
                                                            formMeta.formName +
                                                            '-' +
                                                            formMeta.fields.deliveryTelephone.name +
                                                            '-error'
                                                        }
                                                    />
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
                                                        id={
                                                            formMeta.formName +
                                                            '-' +
                                                            formMeta.fields.deliveryStreet.name
                                                        }
                                                        name={formMeta.fields.deliveryStreet.name}
                                                        label={formMeta.fields.deliveryStreet.label}
                                                        type="text"
                                                        required={true}
                                                        isTouched={isTouched}
                                                        hasError={invalid}
                                                        fieldRef={field}
                                                        disabled={pickupPlace !== null}
                                                    />
                                                    <FormLineError
                                                        error={error}
                                                        inputType="text-input"
                                                        data-testid={
                                                            formMeta.formName +
                                                            '-' +
                                                            formMeta.fields.deliveryStreet.name +
                                                            '-error'
                                                        }
                                                    />
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
                                                            id={
                                                                formMeta.formName +
                                                                '-' +
                                                                formMeta.fields.deliveryCity.name
                                                            }
                                                            name={formMeta.fields.deliveryCity.name}
                                                            label={formMeta.fields.deliveryCity.label}
                                                            required={true}
                                                            type="text"
                                                            isTouched={isTouched}
                                                            hasError={invalid}
                                                            fieldRef={field}
                                                            disabled={pickupPlace !== null}
                                                        />
                                                        <FormLineError
                                                            error={error}
                                                            inputType="text-input"
                                                            data-testid={
                                                                formMeta.formName +
                                                                '-' +
                                                                formMeta.fields.deliveryCity.name +
                                                                '-error'
                                                            }
                                                        />
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
                                                            id={
                                                                formMeta.formName +
                                                                '-' +
                                                                formMeta.fields.deliveryPostcode.name
                                                            }
                                                            name={formMeta.fields.deliveryPostcode.name}
                                                            label={formMeta.fields.deliveryPostcode.label}
                                                            required={true}
                                                            type="text"
                                                            isTouched={isTouched}
                                                            hasError={invalid}
                                                            fieldRef={field}
                                                            disabled={pickupPlace !== null}
                                                        />
                                                        <FormLineError
                                                            error={error}
                                                            inputType="text-input"
                                                            data-testid={
                                                                formMeta.formName +
                                                                '-' +
                                                                formMeta.fields.deliveryPostcode.name +
                                                                '-error'
                                                            }
                                                        />
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
                                                        label={formMeta.fields.deliveryCountry.label}
                                                        hasError={invalid}
                                                        options={countrySelectOptions}
                                                        onChange={field.onChange}
                                                        isDisabled={pickupPlace !== null}
                                                        value={countrySelectOptions.find(
                                                            (option) => option.value === field.value.value,
                                                        )}
                                                        fieldRef={field}
                                                    />

                                                    <FormLineError
                                                        error={error}
                                                        inputType="select"
                                                        data-testid={
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
                        </ContactInformationDeliveryAddressContentStyled>
                    </div>
                </CSSTransition>
            </ContactInformationDeliveryAddressStyled>
        </>
    );
};

/* @component */
export default ContactInformationDeliveryAddress;
