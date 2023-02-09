import { ImageSizesFragmentApi } from 'graphql/generated';

export type SliderItemType = {
    uuid: string;
    name: string;
    link: string;
    extendedText: string;
    extendedTextLink: string;
    webImages: ImageSizesFragmentApi[];
    mobileImages: ImageSizesFragmentApi[];
};
