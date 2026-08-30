import { TypeCoordinates } from 'graphql/types';
import { useEffect } from 'react';
import { useSessionStore } from 'store/useSessionStore';

const GEOLOCATION_MAXIMUM_AGE_MS = 300000;
const GEOLOCATION_TIMEOUT_MS = 10000;

/**
 * Returns the coordinates of the visitor shared across the session, asking the browser for them once when
 * they are not known yet — every store listing (stores page, pickup place selection, delivery options
 * popup) therefore sorts the stores by the distance from the same place
 */
export const useUserCoordinates = (): TypeCoordinates | null => {
    const coordinates = useSessionStore((s) => s.coordinates);
    const updateCoordinates = useSessionStore((s) => s.updateCoordinates);

    useEffect(() => {
        if (coordinates !== null) {
            return;
        }

        if (typeof navigator === 'undefined' || !navigator.geolocation) {
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                updateCoordinates({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                });
            },
            undefined,
            {
                maximumAge: GEOLOCATION_MAXIMUM_AGE_MS,
                timeout: GEOLOCATION_TIMEOUT_MS,
            },
        );
    }, [coordinates, updateCoordinates]);

    return coordinates;
};
