import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useEffect } from 'react';
import { Control, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const useHandleContactInformationNonTextChanges = (
    control: Control<ContactInformation>,
    formMeta: ReturnType<typeof useContactInformationFormMeta>,
): void => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const [
        customer,
        country,
        differentDeliveryAddress,
        deliveryCountry,
        newsletterSubscription,
        isWithoutHeurekaAgreement,
    ] = useWatch({
        name: [
            formMeta.fields.customer.name,
            formMeta.fields.country.name,
            formMeta.fields.differentDeliveryAddress.name,
            formMeta.fields.deliveryCountry.name,
            formMeta.fields.newsletterSubscription.name,
            formMeta.fields.isWithoutHeurekaAgreement.name,
        ],
        control,
    });

    useEffect(() => {
        updateContactInformation({ customer });
    }, [customer]);

    useEffect(() => {
        updateContactInformation({ country });
    }, [country]);

    useEffect(() => {
        updateContactInformation({ differentDeliveryAddress });
    }, [differentDeliveryAddress]);

    useEffect(() => {
        updateContactInformation({ deliveryCountry });
    }, [deliveryCountry]);

    useEffect(() => {
        updateContactInformation({ newsletterSubscription });
    }, [newsletterSubscription]);

    useEffect(() => {
        updateContactInformation({ isWithoutHeurekaAgreement });
    }, [newsletterSubscription]);
};
