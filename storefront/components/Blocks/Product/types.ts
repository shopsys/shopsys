import { FilterOptionsType } from 'types/productFilter';
import { ImageType } from 'types/image';

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

export type PageInfoType = {
    startCursor: string;
    endCursor: string;
    hasNextPage: boolean;
    hasPreviousPage: boolean;
};

export type SliderProductItemType = {
    uuid: string;
    slug: string;
    name: string;
    flags: FlagType[];
    image: ImageType | null;
    stockQuantity: number;
    price: ProductPriceType;
    isMainVariant: boolean;
    availability: string;
    availableStoresCount: number;
    exposedStoresCount: number;
    catalogNumber: string;
};

export type ListedProductItemType = {
    uuid: string;
    slug: string;
    name: string;
    flags: FlagType[];
    image: ImageType | null;
    stockQuantity: number;
    price: ProductPriceType;
    isMainVariant: boolean;
    availability: string;
    availableStoresCount: number;
    exposedStoresCount: number;
    catalogNumber: string;
};

export type ListedProductEdgesType = {
    edges: {
        node: ListedProductItemType;
    }[];
    productFilterOptions: FilterOptionsType | null;
    totalCount: number;
    pageInfo: PageInfoType;
};
