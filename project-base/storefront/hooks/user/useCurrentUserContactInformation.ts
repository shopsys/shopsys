import { useCurrentCustomerContactInformationQuery } from 'connectors/customer/CurrentCustomerUser';
import { useMemo } from 'react';
import { ContactInformation } from 'store/zustand/slices/createContactInformationSlice';
import { usePersistStore } from 'store/zustand/usePersistStore';

export const useCurrentUserContactInformation = (): ContactInformation => {
    const currentUserContactInformationApiData = useCurrentCustomerContactInformationQuery();
    const currentUserContactInformationFromStore = usePersistStore((store) => store.contactInformation);

    const currentUserContactInformationFromApi = useMemo<Partial<ContactInformation>>(
        () => ({
            ...(currentUserContactInformationApiData ? currentUserContactInformationApiData : {}),
        }),
        [currentUserContactInformationApiData],
    );

    return useMemo(
        () =>
            mergeCurrentUserContactInformationFromApiAndStore(
                currentUserContactInformationFromApi,
                currentUserContactInformationFromStore,
            ),
        [currentUserContactInformationFromApi, currentUserContactInformationFromStore],
    );
};

const mergeCurrentUserContactInformationFromApiAndStore = (
    currentUserContactInformationFromApi: Partial<ContactInformation>,
    currentUserContactInformationFromStore: ContactInformation,
): ContactInformation => {
    const filteredCurrentUserContactInformationFromStore: ContactInformation = {
        ...currentUserContactInformationFromStore,
    };

    for (const key in filteredCurrentUserContactInformationFromStore) {
        const filteredProperty = filteredCurrentUserContactInformationFromStore[key as keyof ContactInformation];

        const isEmptyString = typeof filteredProperty === 'string' && filteredProperty.length === 0;
        if (isEmptyString && key in currentUserContactInformationFromApi) {
            delete filteredCurrentUserContactInformationFromStore[key as keyof ContactInformation];
        }
    }

    return {
        ...currentUserContactInformationFromApi,
        ...filteredCurrentUserContactInformationFromStore,
    } as ContactInformation;
};
