import { ImageSizeType } from 'types/image';

export type SliderItemType = {
    uuid: string;
    name: string;
    link: string;
    extendedText: string;
    extendedTextLink: string;
    image: ImageSizeType | null;
};
