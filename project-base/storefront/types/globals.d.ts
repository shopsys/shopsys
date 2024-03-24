import { FunctionComponent, ReactNode, SVGProps } from 'react';

export type FunctionComponentProps = {
    className?: string;
    tid?: string;
    children?: ReactNode;
};

declare module 'react' {
    interface HTMLAttributes<T> extends DOMAttributes<T> {
        tid?: string;
    }
}

declare global {
    type FC<P = object> = FunctionComponent<P & FunctionComponentProps>;
    type SvgFC<P = object> = FC<P & SVGProps<SVGSVGElement>>;

    interface Window {
        Packeta: {
            Viewport: {
                element: null;
                originalValue: null;
                set: () => void;
                restore: () => void;
            };
            Util: {
                makeRequest: PacketeryMakeRequestFunction;
            };
            Widget: {
                baseUrl: string;
                healthUrl: string;
                versions: {
                    v5: 'v5';
                    v6: 'v6';
                };
                close: () => void;
                pick: PacketeryPickFunction;
            };
        };
        dataLayer: any[] | undefined;
    }

    const Loader: SeznamMapLoaderBase & {
        load: (key: string | null, config: SeznamMapAPILoaderConfig | null, onLoad: () => void) => void;
    };

    // eslint-disable-next-line @typescript-eslint/no-namespace
    namespace JAK {
        class Signals {
            constructor();
            addListener: (window: Window, event: SeznamMapEventName, cb: (event?: SeznamMapEvent) => void) => string;
            removeListener: (window: Window, event: SeznamMapEventName, listenerId: string) => void;
        }
    }

    // eslint-disable-next-line @typescript-eslint/no-namespace
    namespace SMap {
        const DEF_BASE: 1;

        const MOUSE_PAN: number;
        const MOUSE_ZOOM: number;
        const MOUSE_WHEEL: number;

        type MarkerCluster = {
            addMarker: (marker: unknown) => void;
            removeMarker: (marker: unknown) => void;
        };

        type MarkerClusterer = {
            addMarker: (marker: unknown) => void;
            removeMarker: (marker: unknown) => void;
        };

        type LayerMarker = {
            addMarker: (marker: Array<SMap.Marker>) => void;
            setClusterer(clusterer: MarkerClusterer | null): void;
            getMarkers: () => Array<SMap.Marker>;
            enable(): void;
            removeAll(): void;
        };

        class Coords {
            constructor(x: number, y: number);
            static fromWGS84(lng: number, lat: number): SMap.Coords;
        }

        class Layer {
            static Marker: {
                new (options?: SeznamMapLayerMarkerOptions): LayerMarker;
            };
            enable(): void;
        }

        class Control {
            static Zoom: {
                new (labels: Record<number, string>, options?: SeznamMapZoomControlOptions): SMap.Control;
            };
            static Mouse: {
                new (mode?: number, options?: SeznamMapMouseOptions): SMap.Control;
            };
        }

        class Marker {
            constructor(coords: SMap.Coords, id: string | false, options?: SeznamMapMarkerOptions);
            static Clusterer: {
                new (map: SMap, maxDistance?: number): MarkerClusterer;
            };
            getId(): string;
        }
    }

    class SMap {
        constructor(container: HTMLElement, center: SMap.Coords, zoom?: number, options?: SeznamMapMapOptions);

        addDefaultLayer(id: number): SMap.Layer;
        addLayer(layer: SMap.Layer): SMap.Layer;
        removeLayer(layer: SMap.Layer): void;

        addControl(control: SMap.Control, options?: SeznamMapControlOptions): void;
        removeControl(control: SMap.Control): void;

        getSignals(): JAK.Signals;

        getZoom(): number;
        getCenter(): SMap.Coords;
        setCenterZoom(center: SMap.Coords, zoom: number, animate?: boolean): void;
        computeCenterZoom(coords: Array<SMap.Coords>, usePadding?: boolean): [SMap.Coords, number];
    }
}

export {};
