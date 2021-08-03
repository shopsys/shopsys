import { ImageType } from '../../basic/ShopsysImage/types';

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

export type ProductItemType = {
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
