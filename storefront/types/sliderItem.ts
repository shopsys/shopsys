import { ImageType } from 'types/image';

export type SliderItemType = {
    uuid: string;
    name: string;
    link: string;
    extendedText: string;
    extendedTextLink: string;
    image: ImageType | null;
};
