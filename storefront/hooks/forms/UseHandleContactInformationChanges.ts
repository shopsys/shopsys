import { contactInformationActions, ContactInformationFormType } from 'redux/slices/contactInformation';
import { Control, useWatch } from 'react-hook-form';
import { useEffect } from 'react';
import { useShopsysDispatch } from 'redux/main';

export const useHandleContactInformationChanges = (
    control: Control<ContactInformationFormType>,
    contactInformationValues: ContactInformationFormType,
): void => {
    const dispatch = useShopsysDispatch();
    const emailValue = useWatch({ name: 'email', defaultValue: contactInformationValues.email, control });
    const registerValue = useWatch({ name: 'register', defaultValue: contactInformationValues.register, control });
    const passwordFirstValue = useWatch({
        name: 'passwordFirst',
        defaultValue: contactInformationValues.passwordFirst,
        control,
    });
    const passwordSecondValue = useWatch({
        name: 'passwordSecond',
        defaultValue: contactInformationValues.passwordSecond,
        control,
    });
    const customerValue = useWatch({ name: 'customer', defaultValue: contactInformationValues.customer, control });
    const telephoneValue = useWatch({ name: 'telephone', defaultValue: contactInformationValues.telephone, control });
    const firstNameValue = useWatch({ name: 'firstName', defaultValue: contactInformationValues.firstName, control });
    const lastNameValue = useWatch({ name: 'lastName', defaultValue: contactInformationValues.lastName, control });
    const streetValue = useWatch({ name: 'street', defaultValue: contactInformationValues.street, control });
    const cityValue = useWatch({ name: 'city', defaultValue: contactInformationValues.city, control });
    const postcodeValue = useWatch({ name: 'postcode', defaultValue: contactInformationValues.postcode, control });
    const countryValue = useWatch({ name: 'country', defaultValue: contactInformationValues.country, control });
    const companyNameValue = useWatch({
        name: 'companyName',
        defaultValue: contactInformationValues.companyName,
        control,
    });
    const companyNumberValue = useWatch({
        name: 'companyNumber',
        defaultValue: contactInformationValues.companyNumber,
        control,
    });
    const companyTaxNumberValue = useWatch({
        name: 'companyTaxNumber',
        defaultValue: contactInformationValues.companyTaxNumber,
        control,
    });
    const differentDeliveryAddressValue = useWatch({
        name: 'differentDeliveryAddress',
        defaultValue: contactInformationValues.differentDeliveryAddress,
        control,
    });
    const deliveryFirstNameValue = useWatch({
        name: 'deliveryFirstName',
        defaultValue: contactInformationValues.deliveryFirstName,
        control,
    });
    const deliveryLastNameValue = useWatch({
        name: 'deliveryLastName',
        defaultValue: contactInformationValues.deliveryLastName,
        control,
    });
    const deliveryCompanyNameValue = useWatch({
        name: 'deliveryCompanyName',
        defaultValue: contactInformationValues.deliveryCompanyName,
        control,
    });
    const deliveryTelephoneValue = useWatch({
        name: 'deliveryTelephone',
        defaultValue: contactInformationValues.deliveryTelephone,
        control,
    });
    const deliveryStreetValue = useWatch({
        name: 'deliveryStreet',
        defaultValue: contactInformationValues.deliveryStreet,
        control,
    });
    const deliveryCityValue = useWatch({
        name: 'deliveryCity',
        defaultValue: contactInformationValues.deliveryCity,
        control,
    });
    const deliveryPostcodeValue = useWatch({
        name: 'deliveryPostcode',
        defaultValue: contactInformationValues.deliveryPostcode,
        control,
    });
    const deliveryCountryValue = useWatch({
        name: 'deliveryCountry',
        defaultValue: contactInformationValues.deliveryCountry,
        control,
    });
    const newsletterSubscriptionValue = useWatch({
        name: 'newsletterSubscription',
        defaultValue: contactInformationValues.newsletterSubscription,
        control,
    });
    useEffect(() => {
        dispatch(contactInformationActions.setEmail(emailValue));
    }, [emailValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setRegister(registerValue));
    }, [registerValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setPasswordFirst(passwordFirstValue));
    }, [passwordFirstValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setPasswordSecond(passwordSecondValue));
    }, [passwordSecondValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setCustomer(customerValue));
    }, [customerValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setTelephone(telephoneValue));
    }, [telephoneValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setFirstName(firstNameValue));
    }, [firstNameValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setLastName(lastNameValue));
    }, [lastNameValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setStreet(streetValue));
    }, [streetValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setCity(cityValue));
    }, [cityValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setPostcode(postcodeValue));
    }, [postcodeValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setCountry(countryValue));
    }, [countryValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setCompanyName(companyNameValue));
    }, [companyNameValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setCompanyNumber(companyNumberValue));
    }, [companyNumberValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setCompanyTaxNumber(companyTaxNumberValue));
    }, [companyTaxNumberValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDifferentDeliveryAddress(differentDeliveryAddressValue));
    }, [differentDeliveryAddressValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDeliveryFirstName(deliveryFirstNameValue));
    }, [deliveryFirstNameValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDeliveryLastName(deliveryLastNameValue));
    }, [deliveryLastNameValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDeliveryCompanyName(deliveryCompanyNameValue));
    }, [deliveryCompanyNameValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDeliveryTelephone(deliveryTelephoneValue));
    }, [deliveryTelephoneValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDeliveryStreet(deliveryStreetValue));
    }, [deliveryStreetValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDeliveryCity(deliveryCityValue));
    }, [deliveryCityValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDeliveryPostcode(deliveryPostcodeValue));
    }, [deliveryPostcodeValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDeliveryCountry(deliveryCountryValue));
    }, [deliveryCountryValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setNewsletterSubscription(newsletterSubscriptionValue));
    }, [newsletterSubscriptionValue]);
};
