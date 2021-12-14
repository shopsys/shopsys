import { FlagType, ProductPriceType } from 'types/product';
import { TransportInputType } from 'types/transport';
import { ImageType } from 'types/image';
import { PaymentInputType } from 'types/payment';
import { PriceType } from 'types/price';

export type CartInput = {
    cartUuid: string | null;
    isCartEmpty: boolean;
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
    uuid: string | null;
    items: CartItemType[];
    totalPrice: PriceType;
    totalDiscountPrice: PriceType;
    remainingAmountWithVatForFreeTransport: number | null;
};

export type AddToCartPopupDataType = {
    name: string;
    slug: string;
    image: ImageType | null;
    quantity: number;
    unitName: string;
    price: ProductPriceType;
};
