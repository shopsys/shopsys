export type MapMarkerNullable = {
    identifier?: string;
    name?: string | null;
    latitude: string | null;
    longitude: string | null;
};

export type MapMarker = {
    identifier?: string;
    name?: string | null;
    latitude: string;
    longitude: string;
};
