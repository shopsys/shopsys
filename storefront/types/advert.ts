import { ImageType } from 'types/image';
import { SimpleCategoryType } from 'types/category';

export type AdvertType = AdvertImageType | AdvertCodeType;

type AdvertCommonType = {
    uuid: string;
    type: string;
    positionName: string;
    name: string;
    categories: SimpleCategoryType[];
};

type AdvertImageType = AdvertCommonType & {
    __typename: 'AdvertImage';
    image: ImageType | null;
    imageMobile: ImageType | null;
    link?: string;
};

type AdvertCodeType = AdvertCommonType & {
    __typename: 'AdvertCode';
    code: string;
};
