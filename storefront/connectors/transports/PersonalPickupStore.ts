import { StoreType, TransportType } from './types';

export const getSelectedPersonalPickupStore = (
    transport: TransportType | null,
    personalPickupStoreUuid: string | null,
): StoreType | null => {
    if (transport === null || personalPickupStoreUuid === null) {
        return null;
    }

    const personalPickupStore = transport.stores.find((store) => store.uuid === personalPickupStoreUuid);
    return personalPickupStore === undefined ? null : personalPickupStore;
};
