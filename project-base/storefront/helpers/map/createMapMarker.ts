import { MapMarker } from 'types/map';

export const createMapMarker = (lat: string | null, lng: string | null, id?: string | null): MapMarker | null => {
    if (!lat || !lng) {
        return null;
    }

    return {
        lat: parseFloat(lat),
        lng: parseFloat(lng),
        id: id ?? false,
    };
};
