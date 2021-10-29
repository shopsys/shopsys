export type ImageType = {
    size: string;
    url: string;
    width: number;
    height: number;
};

export type ImageApiType = {
    position?: number | null;
    sizes: ImageType[];
};
