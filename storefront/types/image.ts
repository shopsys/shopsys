export type ImageSizeType = {
    size: string;
    url: string;
    width: number;
    height: number;
};

export type ImageSizesType = {
    [sizeName: string]: ImageSizeType;
};
