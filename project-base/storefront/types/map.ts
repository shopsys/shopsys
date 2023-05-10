export type LatLngLiteral = {
    lat: number;
    lng: number;
};

export type MapMarker = LatLngLiteral & {
    id: string | false;
};
