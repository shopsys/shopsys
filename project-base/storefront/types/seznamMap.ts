export type SeznamMapLoaderMode = 'single' | 'multi';
export type SeznamMapLoaderAPIMode = 'full' | 'simple';
export type SeznamMapLoaderLang = 'cs' | 'de' | 'en' | 'sk' | 'pl' | 'ru' | 'uk';
type SeznamMapMarkerEventName = 'marker-click';

export type SeznamMapAPILoaderConfig = {
    jak?: boolean;
    poi?: boolean;
    pano?: boolean;
    suggest?: boolean;
    api?: SeznamMapLoaderAPIMode;
};

export type SeznamMapLoaderBase = {
    apiKey: string;
    lang: SeznamMapLoaderLang;
    mode: SeznamMapLoaderMode;
    async: boolean;
};

export type SeznamMapLoaderConfig = Partial<SeznamMapLoaderBase>;

export type SeznamMapMapOptions = {
    minZoom?: number;
    maxZoom?: number;
    orientation?: number;
    projection?: number;
    animTime?: number;
    zoomTime?: number;
    rotationTime?: number;
    ophotoDate?: number;
};

export type SeznamMapControlOptions = {
    left?: number;
    top?: number;
    right?: number;
    bottom?: number;
    anchor?: string;
    visible?: boolean;
};

export type SeznamMapZoomControlOptions = {
    step?: number;
    titles?: Array<string>;
    sliderHeight?: Array<string>;
    showZoomMenu?: boolean;
};

export type SeznamMapMouseOptions = {
    scrollDelay?: number;
    idleDelay?: number;
    minDriftSpeed?: number;
    maxDriftSpeed?: number;
    driftSlowdown?: number;
    driftThreshold?: number;
};

export type SeznamMapLayerMarkerOptions = {
    prefetch: number;
    supportsAnimation: boolean;
    poiTooltip: boolean;
};

export type SeznamMapClusterOptions = {
    maxDistance?: number;
    shouldUseClustering: boolean;
};

export type SeznamMapMarkerOptions = {
    title?: string;
    size?: Array<number> | null;
    url?: HTMLElement | string;
};

export type SeznamMapEvent = {
    target?: SMap.Marker;
};

export type SeznamMapEventName = SeznamMapMarkerEventName;
