import { FlagType, PriceApiType, ProductPriceType } from 'components/Blocks/Product/types';
import { ImageType } from 'components/Basic/Image/types';

export type ProductCartItemType = {
    uuid: string;
    slug: string;
    fullName: string;
    flags: FlagType[];
    image: ImageType | null;
    price: ProductPriceType;
    availability: string;
    stockQuantity: number;
    availableStoresCount: number;
    catalogNumber: string;
};

export type CartItemType = {
    uuid: string;
    product: ProductCartItemType;
    quantity: number;
};

export type CartType = {
    uuid: string;
    items: CartItemType[];
};

export type ProductCartItemApiType = {
    uuid: string;
    slug: string;
    fullName: string;
    flags: FlagType[];
    images: ImageType[];
    price: PriceApiType;
    availability: {
        name: string;
    };
    stockQuantity: number;
    availableStoresCount: number;
    catalogNumber: string;
};

export type CartItemApiType = {
    uuid: string;
    product: ProductCartItemApiType;
    quantity: number;
};

export type CartApiType = {
    uuid: string;
    items: CartItemApiType[];
};

export type AddProductResultType = {
    notOnStockQuantity: number;
    overLimitQuantity: number;
    isQuantityOverLimit: number;
    addedQuantity: number;
};
