import { GtmShippingInfoType } from 'gtm/types/objects';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export const mapGtmShippingInfo = (pickupPlace: StoreOrPacketeryPoint | null): GtmShippingInfoType => {
    let transportDetail = '';
    const transportExtra = [];

    if (pickupPlace !== null) {
        transportDetail = `${pickupPlace.name}, ${pickupPlace.street}, ${pickupPlace.city}, ${pickupPlace.country.name}, ${pickupPlace.postcode}`;

        transportExtra.push('');
    }

    return {
        transportDetail,
        transportExtra,
    };
};
