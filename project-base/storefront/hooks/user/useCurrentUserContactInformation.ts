import { useCurrentCustomerContactInformationQuery } from 'connectors/customer/CurrentCustomerUser';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const useCurrentUserContactInformation = (): ContactInformation => {
    const contactInformationApiData = useCurrentCustomerContactInformationQuery();
    const contactInformationFromStore = usePersistStore((store) => store.contactInformation);

    return mergeContactInformation(contactInformationApiData || {}, contactInformationFromStore);
};

const mergeContactInformation = (
    contactInformationFromApi: Partial<ContactInformation>,
    contactInformationFromStore: ContactInformation,
): ContactInformation => {
    const filteredContactInformationFromStore: ContactInformation = {
        ...contactInformationFromStore,
    };

    for (const key in filteredContactInformationFromStore) {
        const filteredProperty = filteredContactInformationFromStore[key as keyof ContactInformation];

        const isEmptyString = typeof filteredProperty === 'string' && filteredProperty.length === 0;
        if (isEmptyString && key in contactInformationFromApi) {
            delete filteredContactInformationFromStore[key as keyof ContactInformation];
        }
    }

    return {
        ...contactInformationFromApi,
        ...filteredContactInformationFromStore,
    } as ContactInformation;
};
