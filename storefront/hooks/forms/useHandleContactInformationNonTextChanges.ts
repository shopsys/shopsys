import { Control, useWatch } from 'react-hook-form';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useEffect } from 'react';
import { useShopsysDispatch } from 'redux/main';

export const useHandleContactInformationNonTextChanges = (
    control: Control<ContactInformationFormType>,
    formMeta: ReturnType<typeof useContactInformationFormMeta>,
): void => {
    const dispatch = useShopsysDispatch();
    const [
        registerValue,
        customerValue,
        countryValue,
        differentDeliveryAddressValue,
        deliveryCountryValue,
        newsletterSubscriptionValue,
    ] = useWatch({
        name: [
            formMeta.fields.register.name,
            formMeta.fields.customer.name,
            formMeta.fields.country.name,
            formMeta.fields.differentDeliveryAddress.name,
            formMeta.fields.deliveryCountry.name,
            formMeta.fields.newsletterSubscription.name,
        ],
        control,
    });

    useEffect(() => {
        dispatch(contactInformationActions.setRegister(registerValue));
    }, [dispatch, registerValue]);
    useEffect(() => {
        dispatch(contactInformationActions.setCustomer(customerValue));
    }, [customerValue, dispatch]);
    useEffect(() => {
        dispatch(contactInformationActions.setCountry(countryValue));
    }, [countryValue, dispatch]);
    useEffect(() => {
        dispatch(contactInformationActions.setDifferentDeliveryAddress(differentDeliveryAddressValue));
    }, [differentDeliveryAddressValue, dispatch]);
    useEffect(() => {
        dispatch(contactInformationActions.setDeliveryCountry(deliveryCountryValue));
    }, [deliveryCountryValue, dispatch]);
    useEffect(() => {
        dispatch(contactInformationActions.setNewsletterSubscription(newsletterSubscriptionValue));
    }, [dispatch, newsletterSubscriptionValue]);
};
