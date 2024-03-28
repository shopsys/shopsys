import { PacketeryExtendedPoint, PacketeryPickFunction } from './types';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';

/**
 * @see https://docs.packetery.com/01-pickup-point-selection/02-widget-v6.html
 */

export const packeteryPick: PacketeryPickFunction = (apiKey, callback, opts, inElement) => {
    let defaultInElement: HTMLElement | undefined | null = inElement;

    if (defaultInElement === undefined) {
        defaultInElement = document.getElementById('packetery-container');
    }

    if (defaultInElement === null) {
        return;
    }

    window.Packeta.Widget.pick(apiKey, callback, opts, inElement);
};

export const mapPacketeryExtendedPoint = (packeteryExtendedPoint: PacketeryExtendedPoint): TypeListedStoreFragment => ({
    __typename: 'Store',
    slug: '',
    locationLatitude: null,
    locationLongitude: null,
    identifier: packeteryExtendedPoint.id.toString(),
    description: packeteryExtendedPoint.directions,
    name: packeteryExtendedPoint.name,
    city: packeteryExtendedPoint.city,
    street: packeteryExtendedPoint.street,
    country: {
        __typename: 'Country',
        code: packeteryExtendedPoint.country.toUpperCase(),
        name: packeteryExtendedPoint.country.toUpperCase(),
    },
    postcode: packeteryExtendedPoint.zip.replaceAll(' ', ''),
    openingHours: {
        isOpen: false,
        dayOfWeek: 0,
        openingHoursOfDays: [],
    },
});
