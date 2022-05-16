import { ImageType } from './image';
import { PaymentType } from './payment';
import { PickupPlaceType } from './pickupPlace';
import { TransportType } from './transport';
import { SimpleFlagType } from 'types/flag';
import { PriceType, ProductPriceType } from 'types/price';
import { SimpleProductType } from 'types/product';

export type CurrentCartType = {
    cart: CartType | null;
    isCartEmpty: boolean;
    transport: TransportType | null;
    pickupPlace: PickupPlaceType | null;
    payment: PaymentType | null;
    paymentGoPayBankSwift: string | null;
    promoCode: string | null;
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

export type AddToCartPopupDataType = SimpleProductType & {
    quantity: number;
};
