import { PickupPlaceType, TransportType } from './types';
import { getPacketeryCookie } from 'helpers/packetery';

export const getSelectedPickupPlace = (
    transport: TransportType | null,
    pickupPlaceIdentifier: string | null,
): PickupPlaceType | null => {
    if (transport === null || pickupPlaceIdentifier === null) {
        return null;
    }

    if (transport.transportType.code === 'packetery') {
        return getPacketeryCookie();
    }

    const pickupPlace = transport.stores.find((place) => place.identifier === pickupPlaceIdentifier);
    return pickupPlace === undefined ? null : pickupPlace;
};
