import { StoreType, TransportType } from './types';

export const getSelectedPersonalPickupStore = (
    transport: TransportType | null,
    pickupPlaceIdentifier: string | null,
): StoreType | null => {
    if (transport === null || pickupPlaceIdentifier === null) {
        return null;
    }

    const personalPickupStore = transport.stores.find((store) => store.uuid === pickupPlaceIdentifier);
    return personalPickupStore === undefined ? null : personalPickupStore;
};
