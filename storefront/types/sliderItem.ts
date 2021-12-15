import { ImageApiType } from 'types/image';

export type SliderItem = {
    uuid: string;
    name: string;
    link: string;
    extendedText: string;
    extendedTextLink: string;
    images: ImageApiType[];
};
