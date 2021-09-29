import { FlagType, PriceApiType, ProductPriceType } from 'components/Blocks/Product/types';
import { ImageApiType, ImageType } from 'components/Basic/Image/types';

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
    unit: {
        name: string;
    };
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
    images: ImageApiType[];
    price: PriceApiType;
    availability: {
        name: string;
    };
    stockQuantity: number;
    availableStoresCount: number;
    catalogNumber: string;
    unit: {
        name: string;
    };
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
    isNew: boolean;
    isQuantityOverLimit: number;
    addedQuantity: number;
};

export type AddToCartResultType = CartApiType & { addProductResult: AddProductResultType };
