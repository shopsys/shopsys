export type ImageType = {
    url: string;
    width: number;
    height: number;
};

export type ImageApiType = {
    position?: string | null;
    sizes: ImageType[];
};
