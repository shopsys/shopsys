export type AdvertType = AdvertImageType | AdvertCodeType;
import { ImageSizeType } from 'types/image';

type AdvertImageType = {
    __typename: 'AdvertImage';
    image: ImageSizeType | null;
    imageMobile: ImageSizeType | null;
    link?: string;
    name: string;
    positionName: string;
    type: string;
    uuid: string;
};

type AdvertCodeType = {
    __typename: 'AdvertCode';
    code: string;
    uuid: string;
    name: string;
    positionName: string;
    type: string;
};
