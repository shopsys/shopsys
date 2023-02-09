import { ImageSizesFragmentApi, SimpleCategoryFragmentApi } from 'graphql/generated';

export type AdvertType = AdvertImageType | AdvertCodeType;

type AdvertCommonType = {
    uuid: string;
    type: string;
    positionName: string;
    name: string;
    categories: SimpleCategoryFragmentApi[];
};

type AdvertImageType = AdvertCommonType & {
    __typename: 'AdvertImage';
    image: ImageSizesFragmentApi[];
    imageMobile: ImageSizesFragmentApi[];
    link?: string;
};

type AdvertCodeType = AdvertCommonType & {
    __typename: 'AdvertCode';
    code: string;
};
