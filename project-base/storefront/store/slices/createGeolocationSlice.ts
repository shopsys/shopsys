import { TypeCoordinates } from 'graphql/types';
import { StateCreator } from 'zustand';

export type GeolocationSlice = {
    coordinates: TypeCoordinates | null;
    updateCoordinates: (coordinates: TypeCoordinates | null) => void;
};

export const createGeolocationSlice: StateCreator<GeolocationSlice> = (set) => ({
    coordinates: null,

    updateCoordinates: (coordinates) => {
        set(() => ({
            coordinates: coordinates,
        }));
    },
});
