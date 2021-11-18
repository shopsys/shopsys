import { StoreType, TransportType } from './types';
import { getPacketeryCookie } from 'helpers/packetery';

export const getSelectedPersonalPickupStore = (
    transport: TransportType | null,
    pickupPlaceIdentifier: string | null,
): StoreType | null => {
    if (transport === null || pickupPlaceIdentifier === null) {
        return null;
    }

    if (transport.transportType.code === 'packetery') {
        return getPacketeryCookie();
    }

    const personalPickupStore = transport.stores.find((store) => store.uuid === pickupPlaceIdentifier);
    return personalPickupStore === undefined ? null : personalPickupStore;
};
