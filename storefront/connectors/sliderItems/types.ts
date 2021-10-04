import { ImageApiType } from 'components/Basic/Image/types';
import { v4 as uuid } from 'uuid';

export type SliderItem = {
    uuid: typeof uuid;
    name: string;
    link: string;
    extendedText: string;
    extendedTextLink: string;
    images: ImageApiType[];
};
