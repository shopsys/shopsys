import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useEffect } from 'react';
import { Control, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/zustand/slices/createContactInformationSlice';
import { usePersistStore } from 'store/zustand/usePersistStore';

export const useHandleContactInformationNonTextChanges = (
    control: Control<ContactInformation>,
    formMeta: ReturnType<typeof useContactInformationFormMeta>,
): void => {
    const updateContactInformationState = usePersistStore((s) => s.updateContactInformationState);
    const [
        customerValue,
        countryValue,
        differentDeliveryAddressValue,
        deliveryCountryValue,
        newsletterSubscriptionValue,
    ] = useWatch({
        name: [
            formMeta.fields.customer.name,
            formMeta.fields.country.name,
            formMeta.fields.differentDeliveryAddress.name,
            formMeta.fields.deliveryCountry.name,
            formMeta.fields.newsletterSubscription.name,
        ],
        control,
    });

    useEffect(() => {
        updateContactInformationState({ customer: customerValue });
    }, [customerValue, updateContactInformationState]);

    useEffect(() => {
        updateContactInformationState({ country: countryValue });
    }, [countryValue, updateContactInformationState]);

    useEffect(() => {
        updateContactInformationState({ differentDeliveryAddress: differentDeliveryAddressValue });
    }, [differentDeliveryAddressValue, updateContactInformationState]);

    useEffect(() => {
        updateContactInformationState({ deliveryCountry: deliveryCountryValue });
    }, [deliveryCountryValue, updateContactInformationState]);

    useEffect(() => {
        updateContactInformationState({ newsletterSubscription: newsletterSubscriptionValue });
    }, [updateContactInformationState, newsletterSubscriptionValue]);
};
