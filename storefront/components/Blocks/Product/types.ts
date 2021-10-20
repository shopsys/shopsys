import { ImageApiType, ImageType } from 'components/Basic/Image/types';

export type FlagType = {
    name: string;
    rgbColor: string;
};

export type ProductPriceApiType = {
    priceWithVat: string;
    priceWithoutVat: string;
    vatAmount: string;
    isPriceFrom: boolean;
};

export type ProductPriceType = {
    priceWithVat: number;
    priceWithoutVat: number;
    vatAmount: number;
    isPriceFrom: boolean;
    currencyCode: string;
};

export type PageInfoType = {
    startCursor: string;
    endCursor: string;
    hasNextPage: boolean;
    hasPreviousPage: boolean;
};

export type SliderProductItemType = {
    uuid: string;
    detailSlug: string;
    name: string;
    flags: FlagType[];
    image: ImageType | null;
    stockQuantity: number;
    price: ProductPriceType;
    isMainVariant: boolean;
    availability: string;
    availableStoresCount: number;
    exposedStoresCount: number;
};

export type ListedProductItemType = {
    uuid: string;
    detailSlug: string;
    name: string;
    flags: FlagType[];
    image: ImageType | null;
    stockQuantity: number;
    price: ProductPriceType;
    isMainVariant: boolean;
    availability: string;
    availableStoresCount: number;
    exposedStoresCount: number;
};

export type ProductItemApiType = {
    __typename: string;
    uuid: string;
    slug: string;
    name: string;
    flags: FlagType[];
    images: ImageApiType[];
    stockQuantity: number;
    availability: {
        name: string;
    };
    price: ProductPriceApiType;
    availableStoresCount: number;
    exposedStoresCount: number;
};

export type ListedProductItemApiType = {
    __typename: string;
    uuid: string;
    slug: string;
    name: string;
    flags: FlagType[];
    images: ImageApiType[];
    stockQuantity: number;
    availability: {
        name: string;
    };
    price: ProductPriceApiType;
    availableStoresCount: number;
    exposedStoresCount: number;
};

export type ListedProductEdgesType = {
    edges: {
        node: ListedProductItemType;
    }[];
    totalCount: number;
    pageInfo: PageInfoType;
};
