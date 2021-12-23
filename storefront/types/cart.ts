import { FlagType, ProductPriceType } from 'types/product';
import { PaymentInputType, PaymentType } from 'types/payment';
import { TransportInputType, TransportType } from 'types/transport';
import { ImageType } from 'types/image';
import { PickupPlaceType } from 'types/pickupPlace';
import { PriceType } from 'types/price';

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
    items: CartItemType[];
    totalPrice: PriceType;
    totalDiscountPrice: PriceType;
    remainingAmountWithVatForFreeTransport: number | null;
};

export type CartResultValues = {
    cartUuid: string | null;
    cart: CartType | null;
    transport: TransportType | null;
    pickupPlace: PickupPlaceType | null;
    payment: PaymentType | null;
    promoCode: string | null;
};

export type AddToCartPopupDataType = {
    name: string;
    slug: string;
    image: ImageType | null;
    quantity: number;
    unitName: string;
    price: ProductPriceType;
};
