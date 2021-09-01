import { v4 as uuid } from 'uuid';

type Image = {
    type: string;
    position: number;
    size: string;
    url: string;
    width: number;
    height: number;
};

export type SliderItem = {
    uuid: typeof uuid;
    name: string;
    link: string;
    extendedText: string;
    extendedTextLink: string;
    images: Image[];
};
