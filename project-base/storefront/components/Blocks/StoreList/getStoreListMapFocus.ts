import { StoreOrPacketeryPoint } from 'utils/packetery/types';

const SINGLE_SEARCH_RESULT_ZOOM = 12;
const MIN_SEARCH_RESULT_ZOOM = 5;
const MAX_SEARCH_RESULT_ZOOM = 12;
const WORLD_DIMENSION_IN_PIXELS = 256;
const VIRTUAL_MAP_DIMENSION_IN_PIXELS = 512;
const MAP_BOUNDS_PADDING_RATIO = 1.4;
const COORDINATES_EQUALITY_THRESHOLD = 0.000001;

type Coordinates = {
    latitude: number;
    longitude: number;
};

type StoreListMapFocus = Coordinates & {
    defaultZoom: number;
    searchCoordinatesMarker: Coordinates | null;
};

export const getStoreListMapFocus = (
    searchCoordinates: Coordinates | null,
    firstStore: StoreOrPacketeryPoint | null,
): StoreListMapFocus | null => {
    if (searchCoordinates === null) {
        return null;
    }

    const firstStoreCoordinates = getStoreCoordinates(firstStore);

    if (firstStoreCoordinates === null || areCoordinatesEqual(searchCoordinates, firstStoreCoordinates)) {
        return {
            ...searchCoordinates,
            defaultZoom: SINGLE_SEARCH_RESULT_ZOOM,
            searchCoordinatesMarker: null,
        };
    }

    return {
        ...getMiddleCoordinates(searchCoordinates, firstStoreCoordinates),
        defaultZoom: getZoomForCoordinates(searchCoordinates, firstStoreCoordinates),
        searchCoordinatesMarker: searchCoordinates,
    };
};

const getStoreCoordinates = (store: StoreOrPacketeryPoint | null): Coordinates | null => {
    if (
        store?.latitude === null ||
        store?.latitude === undefined ||
        store.longitude === null ||
        store.longitude === undefined
    ) {
        return null;
    }

    return {
        latitude: Number(store.latitude),
        longitude: Number(store.longitude),
    };
};

const areCoordinatesEqual = (firstCoordinates: Coordinates, secondCoordinates: Coordinates): boolean =>
    Math.abs(firstCoordinates.latitude - secondCoordinates.latitude) < COORDINATES_EQUALITY_THRESHOLD &&
    Math.abs(firstCoordinates.longitude - secondCoordinates.longitude) < COORDINATES_EQUALITY_THRESHOLD;

const getMiddleCoordinates = (firstCoordinates: Coordinates, secondCoordinates: Coordinates): Coordinates => ({
    latitude: (firstCoordinates.latitude + secondCoordinates.latitude) / 2,
    longitude: (firstCoordinates.longitude + secondCoordinates.longitude) / 2,
});

const getZoomForCoordinates = (firstCoordinates: Coordinates, secondCoordinates: Coordinates): number => {
    const latFraction =
        (getLatitudeRadians(Math.max(firstCoordinates.latitude, secondCoordinates.latitude)) -
            getLatitudeRadians(Math.min(firstCoordinates.latitude, secondCoordinates.latitude))) /
        Math.PI;
    const lngDiff = Math.abs(firstCoordinates.longitude - secondCoordinates.longitude);
    const lngFraction = Math.min(lngDiff, 360 - lngDiff) / 360;
    const zoom = Math.min(getZoom(latFraction), getZoom(lngFraction), MAX_SEARCH_RESULT_ZOOM);

    return Math.max(Math.floor(zoom), MIN_SEARCH_RESULT_ZOOM);
};

const getLatitudeRadians = (latitude: number): number => {
    const sin = Math.sin((latitude * Math.PI) / 180);
    const radians = Math.log((1 + sin) / (1 - sin)) / 2;

    return Math.max(Math.min(radians, Math.PI), -Math.PI) / 2;
};

const getZoom = (mapFraction: number): number => {
    if (mapFraction === 0) {
        return MAX_SEARCH_RESULT_ZOOM;
    }

    return Math.log2(
        VIRTUAL_MAP_DIMENSION_IN_PIXELS / WORLD_DIMENSION_IN_PIXELS / (mapFraction * MAP_BOUNDS_PADDING_RATIO),
    );
};
