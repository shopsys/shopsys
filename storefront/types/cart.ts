import { PaymentInputType, PaymentType } from 'types/payment';
import { PriceType, ProductPriceType } from 'types/price';
import { TransportInputType, TransportType } from 'types/transport';
import { ImageType } from 'types/image';
import { PickupPlaceType } from 'types/pickupPlace';
import { SimpleFlagType } from 'types/flag';
import { SimpleProductType } from 'types/product';

export type CartInput = {
    cartUuid: string | null;
    transport: TransportInputType | null;
    payment: PaymentInputType | null;
};

export type ProductCartItemType = {
    uuid: string;
    slug: string;
    fullName: string;
    flags: SimpleFlagType[];
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
    totalItemsPrice: PriceType;
    totalDiscountPrice: PriceType;
    remainingAmountWithVatForFreeTransport: number | null;
};

export type CartResultValues = {
    cartUuid: string | null;
    cart: CartType | null;
    transport: TransportType | null;
    pickupPlace: PickupPlaceType | null;
    goPayBankSwift: string | null;
    payment: PaymentType | null;
    promoCode: string | null;
};

export type AddToCartPopupDataType = SimpleProductType & {
    quantity: number;
};
