export type ImageAdditionalSize = {
    height: number | null;
    media: string;
    url: string;
    width: number | null;
};

export type ImageSizeType = {
    size: string;
    url: string;
    width: number | null;
    height: number | null;
    additionalSizes: ImageAdditionalSize[];
};

export type ImageType = {
    sizes: ImageSizeType[] | null;
};
