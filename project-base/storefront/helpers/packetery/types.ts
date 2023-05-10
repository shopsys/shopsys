export {};

/**
 * @see https://docs.packetery.com/01-pickup-point-selection/02-widget-v6.html
 */

export type PacketeryExtendedPoint = {
    id: number;
    name: string;
    country: string;
    currency: string;
    place: string;
    special: string;
    street: string;
    city: string;
    zip: string;
    gps: { lat: number; lon: number };
    packetConsignment: boolean;
    claimAssistant: boolean;
    maxWeight: number;
    error: null | 'vacation' | 'full' | 'closing' | 'technical';
    warning: null | 'almostFull';
    recommended: null | 'quick';
    isNew: boolean;
    creditCardPayment: null | boolean;
    saturdayOpenTo: number;
    sundayOpenTo: number;
    businessDaysOpenUpTo: number;
    businessDaysOpenLunchtime: boolean;
    directions: string;
    directionsCar: string;
    directionsPublic: string;
    holidayStart: null | string;
    holidayEnd: null | string;
    wheelchairAccessible: boolean;
    url: string;
    photo: { thumbnail: string; normal: string };
    openingHours: {
        compactShort: string;
        compactLong: string;
        tableLong: string;
        regular: string;
    };
    pickupPointType: string;
    routingCode: string;
    carrierId: string;
    carrierPickupPointId: string;
};

export type PacketeryOptions = {
    webUrl?: string;
    appIdentity?: string;
    country?: string;
    carriers?: string;
    language?: string;
    claimAssistant?: string;
    packetConsignment?: string;
    weight?: number;
};

export type PacketeryMakeRequestFunction = (
    method: string,
    url: string,
    data: any,
    callback: (status: { status: number; statusText: string } | XMLHttpRequest['response'], hasError: boolean) => void,
) => void;

export type PacketeryPickFunction = (
    apiKey: string,
    callback: (point: PacketeryExtendedPoint | null) => void,
    opts?: PacketeryOptions,
    inElement?: HTMLElement,
) => void;
