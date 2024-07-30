import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { onGtmUserEntryEventHandler } from 'gtm/handlers/onGtmUserEntryEventHandler';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useAfterUserEntry = () => {
    const { userEntry, updateUserEntryState } = usePersistStore((store) => store);
    const currentCustomerData = useCurrentCustomerData();

    useEffect(() => {
        if (userEntry && currentCustomerData) {
            onGtmUserEntryEventHandler(
                userEntry === 'login' ? GtmEventType.login : GtmEventType.registration,
                currentCustomerData,
            );
            updateUserEntryState(null);
        }
    }, [userEntry, currentCustomerData]);
};
