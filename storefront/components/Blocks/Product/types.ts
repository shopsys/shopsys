import { ImageType } from '../../Basic/ShopsysImage/types';

export type FlagType = {
    name: string;
    rgbColor: string;
};

export type ProductPriceType = {
    priceWithVat: number;
    priceWithoutVat: number;
    vatAmount: number;
    isPriceFrom: boolean;
    currencyCode: string;
};

export type SliderProductItemType = {
    detailSlug: string;
    name: string;
    flags: FlagType[];
    image: ImageType | null;
    price: ProductPriceType;
    isMainVariant: boolean;
    availability: string;
    availableStoresCount: number;
    exposedStoresCount: number;
};

export type ListedProductItemType = {
    detailSlug: string;
    name: string;
    flags: FlagType[];
    image: ImageType | null;
    price: ProductPriceType;
    isMainVariant: boolean;
    availability: string;
    availableStoresCount: number;
    exposedStoresCount: number;
};

export type ProductItemApiType = {
    __typename: string;
    slug: string;
    name: string;
    flags: FlagType[];
    images: ImageType[];
    availability: {
        name: string;
    };
    price: {
        priceWithVat: number;
        priceWithoutVat: number;
        vatAmount: number;
        isPriceFrom: boolean;
    };
    availableStoresCount: number;
    exposedStoresCount: number;
};

export type ListedProductItemApiType = {
    __typename: string;
    slug: string;
    name: string;
    flags: FlagType[];
    images: ImageType[];
    availability: {
        name: string;
    };
    price: {
        priceWithVat: number;
        priceWithoutVat: number;
        vatAmount: number;
        isPriceFrom: boolean;
    };
    availableStoresCount: number;
    exposedStoresCount: number;
};

export type ListedProductEdgesType = {
    edges: {
        node: ListedProductItemType;
    }[];
    totalCount: number;
};
