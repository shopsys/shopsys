import { PacketeryExtendedPoint, PacketeryPickFunction, StoreOrPacketeryPoint } from './types';
import { TypeOpeningHours } from 'graphql/types';

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

export const mapPacketeryExtendedPoint = (packeteryExtendedPoint: PacketeryExtendedPoint): StoreOrPacketeryPoint => ({
    __typename: 'Store',
    slug: '',
    latitude: null,
    longitude: null,
    identifier: packeteryExtendedPoint.id.toString(),
    description: packeteryExtendedPoint.directions,
    name: packeteryExtendedPoint.place,
    city: packeteryExtendedPoint.city,
    street: packeteryExtendedPoint.street,
    country: {
        __typename: 'Country',
        code: packeteryExtendedPoint.country.toUpperCase(),
        name: packeteryExtendedPoint.country.toUpperCase(),
    },
    postcode: packeteryExtendedPoint.zip.replaceAll(' ', ''),
    openingHours: mapPacketeryOpeningHoursToInternalOpeningHours(packeteryExtendedPoint),
});

// {date: '2024-08-29', hours: '12:00–22:00'}
const mapPacketeryOpeningHoursToInternalOpeningHours = (
    packeteryExtendedPoint: PacketeryExtendedPoint,
): StoreOrPacketeryPoint['openingHours'] => {
    const daysMap = {
        monday: 1,
        tuesday: 2,
        wednesday: 3,
        thursday: 4,
        friday: 5,
        saturday: 6,
        sunday: 7,
    };

    const internalOpeningHours: TypeOpeningHours = {
        isOpen: false,
        dayOfWeek: new Date().getDay(),
        openingHoursOfDays: [],
    };

    const parseTimeRange = (range: string) => {
        const [openingTime, closingTime] = range.split('–');
        return { openingTime, closingTime };
    };

    const parseOpeningHours = (hours: string) => {
        if (hours.toLowerCase() === 'nonstop') {
            return [
                {
                    openingTime: '00:00',
                    closingTime: '23:59',
                },
            ];
        }

        return hours.split(', ').map(parseTimeRange);
    };

    // Process regular hours and fill internalOpeningHours
    for (const [day, hours] of Object.entries(packeteryExtendedPoint.openingHours.regular)) {
        const openingHoursRanges = parseOpeningHours(hours as string);
        const date = new Date();
        date.setDate(date.getDate() + ((daysMap[day as keyof typeof daysMap] - date.getDay() + 7) % 7));
        const isoDate = date.toISOString().split('T')[0];

        internalOpeningHours.openingHoursOfDays.push({
            date: `${isoDate}T00:00:00+02:00`,
            dayOfWeek: daysMap[day as keyof typeof daysMap],
            openingHoursRanges,
        });
    }

    // Sort the days so that the current day is first and the rest follow sequentially
    const currentDayOfWeek = new Date().getDay();
    internalOpeningHours.openingHoursOfDays.sort((a, b) => {
        return ((a.dayOfWeek - currentDayOfWeek + 7) % 7) - ((b.dayOfWeek - currentDayOfWeek + 7) % 7);
    });

    return { ...internalOpeningHours, exceptionDays: packeteryExtendedPoint.exceptionDays };
};
