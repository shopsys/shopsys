import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { GtmShippingInfoType } from 'gtm/types/objects';

export const mapGtmShippingInfo = (pickupPlace: TypeListedStoreFragment | null): GtmShippingInfoType => {
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
