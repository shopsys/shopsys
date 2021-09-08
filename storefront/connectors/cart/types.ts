import { FlagType, ProductPriceType } from 'components/blocks/product/types';
import { ImageType } from 'components/basic/ShopsysImage/types';

export type ProductCartItemType = {
    slug: string;
    name: string;
    flags: FlagType[];
    image: ImageType | null;
    price: ProductPriceType;
    availability: string;
    availableStoresCount: number;
    namePrefix: string;
    nameSuffix: string;
    catalogNumber: string;
    isInSale: boolean;
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
    slug: string;
    name: string;
    flags: FlagType[];
    images: ImageType[];
    price: {
        priceWithVat: number;
        priceWithoutVat: number;
        vatAmount: number;
        isPriceFrom: boolean;
    };
    availability: {
        name: string;
    };
    availableStoresCount: number;
    namePrefix: string;
    nameSuffix: string;
    catalogNumber: string;
    isInSale: boolean;
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
