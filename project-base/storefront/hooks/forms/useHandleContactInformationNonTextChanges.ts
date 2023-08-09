import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useEffect } from 'react';
import { Control, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/zustand/slices/createContactInformationSlice';
import { usePersistStore } from 'store/zustand/usePersistStore';

export const useHandleContactInformationNonTextChanges = (
    control: Control<ContactInformation>,
    formMeta: ReturnType<typeof useContactInformationFormMeta>,
): void => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
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
        updateContactInformation({ customer: customerValue });
    }, [customerValue]);

    useEffect(() => {
        updateContactInformation({ country: countryValue });
    }, [countryValue]);

    useEffect(() => {
        updateContactInformation({ differentDeliveryAddress: differentDeliveryAddressValue });
    }, [differentDeliveryAddressValue]);

    useEffect(() => {
        updateContactInformation({ deliveryCountry: deliveryCountryValue });
    }, [deliveryCountryValue]);

    useEffect(() => {
        updateContactInformation({ newsletterSubscription: newsletterSubscriptionValue });
    }, [newsletterSubscriptionValue]);
};
