import { PacketeryExtendedPoint, PacketeryPickFunction } from './types';
import { canUseDom } from 'helpers/canUseDom';
import nookies from 'nookies';
import { PickupPlaceType } from 'types/pickupPlace';

/**
 * @see https://docs.packetery.com/01-pickup-point-selection/02-widget-v6.html
 */

export const packeteryClose = (): void => {
    if (canUseDom()) {
        window.Packeta.Widget.close();
    }
};

export const packeteryPick: PacketeryPickFunction = (apiKey, callback, opts, inElement) => {
    if (!canUseDom()) {
        return;
    }

    let defaultInElement: HTMLElement | undefined | null = inElement;

    if (defaultInElement === undefined) {
        defaultInElement = document.getElementById('packetery-container');
    }

    if (defaultInElement === null) {
        return;
    }

    window.Packeta.Widget.pick(apiKey, callback, opts, inElement);
};

export const mapPacketeryExtendedPoint = (packeteryExtendedPoint: PacketeryExtendedPoint): PickupPlaceType => ({
    identifier: packeteryExtendedPoint.id.toString(),
    description: packeteryExtendedPoint.directions,
    name: packeteryExtendedPoint.name,
    city: packeteryExtendedPoint.city,
    street: packeteryExtendedPoint.street,
    country: { code: packeteryExtendedPoint.country.toUpperCase(), name: packeteryExtendedPoint.country.toUpperCase() },
    postcode: packeteryExtendedPoint.zip.replaceAll(' ', ''),
    openingHoursHtml: parsePacketeryOpeningHours(packeteryExtendedPoint.openingHours.compactShort),
});

/**
 * only specific HTML tags are filtered
 * @see https://docs.packetery.com/01-pickup-point-selection/02-widget-v6.html#:~:text=PointHours
 */
const parsePacketeryOpeningHours = (openingHours: string) => {
    return openingHours.replaceAll(/<(\/?strong\s?(style='color: red;')?)>/g, '');
};

export const getPacketeryCookie = (): PickupPlaceType | null => {
    const cookies = nookies.get();
    if ('packeteryPickupPoint' in cookies) {
        return JSON.parse(cookies.packeteryPickupPoint);
    }

    return null;
};

export const setPacketeryCookie = (mappedPacketeryPoint: PickupPlaceType): void => {
    nookies.set(undefined, 'packeteryPickupPoint', JSON.stringify(mappedPacketeryPoint), {
        path: '/',
        maxAge: 60 * 60 * 24 * 30,
    });
};

export const removePacketeryCookie = (): void => {
    nookies.destroy(undefined, 'packeteryPickupPoint', {
        path: '/',
    });
};
