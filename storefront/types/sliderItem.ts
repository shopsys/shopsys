import { ImageApiType } from 'components/Basic/Image/types';

export type SliderItem = {
    uuid: string;
    name: string;
    link: string;
    extendedText: string;
    extendedTextLink: string;
    images: ImageApiType[];
};
