import { contactInformationActions, ContactInformationFormType } from 'redux/slices/contactInformation';
import { Control, useWatch } from 'react-hook-form';
import { useEffect } from 'react';
import { useShopsysDispatch } from 'redux/main';

export const useHandleContactInformationNonTextChanges = (
    control: Control<ContactInformationFormType>,
    contactInformationValues: ContactInformationFormType,
): void => {
    const dispatch = useShopsysDispatch();
    const registerValue = useWatch({ name: 'register', defaultValue: contactInformationValues.register, control });
    const customerValue = useWatch({ name: 'customer', defaultValue: contactInformationValues.customer, control });
    const countryValue = useWatch({ name: 'country', defaultValue: contactInformationValues.country, control });
    const differentDeliveryAddressValue = useWatch({
        name: 'differentDeliveryAddress',
        defaultValue: contactInformationValues.differentDeliveryAddress,
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
        dispatch(contactInformationActions.setRegister(registerValue));
    }, [registerValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setCustomer(customerValue));
    }, [customerValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setCountry(countryValue));
    }, [countryValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDifferentDeliveryAddress(differentDeliveryAddressValue));
    }, [differentDeliveryAddressValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setDeliveryCountry(deliveryCountryValue));
    }, [deliveryCountryValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setNewsletterSubscription(newsletterSubscriptionValue));
    }, [newsletterSubscriptionValue]);
};
