type Image = {
    type: string;
    position: number;
    size: string;
    url: string;
    width: number;
    height: number;
};

export type SliderItem = {
    uuid: string;
    name: string;
    link: string;
    extendedText: string;
    extendedTextLink: string;
    images: Image[];
};
