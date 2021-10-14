import { FlagType, ProductPriceApiType, ProductPriceType } from 'components/Blocks/Product/types';
import { ImageApiType, ImageType } from 'components/Basic/Image/types';
import { PaymentApiType, PaymentInputType } from 'connectors/payments/types';
import { PriceApiType, PriceType, TransportApiType, TransportInputType } from 'connectors/transports/types';

export type CartInput = {
    cartUuid: string | null;
    transport: TransportInputType | null;
    payment: PaymentInputType | null;
    promoCode: string | null;
};

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
    totalPrice: PriceType;
    totalDiscountPrice: PriceType;
};

export type ProductCartItemApiType = {
    uuid: string;
    slug: string;
    fullName: string;
    flags: FlagType[];
    images: ImageApiType[];
    price: ProductPriceApiType;
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

export type CartModificationsResultApiType = {
    itemModifications: {
        noLongerListableCartItems: CartItemApiType[];
        cartItemsWithModifiedPrice: CartItemApiType[];
        cartItemsWithChangedQuantity: CartItemApiType[];
        noLongerAvailableCartItemsDueToQuantity: CartItemApiType[];
    };
    transportModifications: {
        transportPriceChanged: boolean;
        transportUnavailable: boolean;
        transportWeightLimitExceeded: boolean;
    };
    paymentModifications: {
        paymentPriceChanged: boolean;
        paymentUnavailable: boolean;
    };
};

export type CartApiType = {
    uuid: string;
    items: CartItemApiType[];
    transport: TransportApiType;
    payment: PaymentApiType;
    totalPrice: PriceApiType;
    totalDiscountPrice: PriceApiType;
    modifications: CartModificationsResultApiType;
};

export type AddProductResultType = {
    notOnStockQuantity: number;
    overLimitQuantity: number;
    isNew: boolean;
    isQuantityOverLimit: number;
    addedQuantity: number;
};

export type AddToCartResultType = CartApiType & {
    addProductResult: AddProductResultType;
};
