import { PriceType, ProductPriceType } from 'types/price';
import { AvailabilityType } from './availability';
import { ImageType } from './image';
import { PaymentType } from './payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { SimpleBrandType } from './brand';
import { SimpleFlagType } from 'types/flag';
import { SimpleProductType } from 'types/product';
import { TransportType } from 'types/transport';

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
    availability: AvailabilityType;
    stockQuantity: number;
    availableStoresCount: number;
    catalogNumber: string;
    unit: {
        name: string;
    };
    brand: SimpleBrandType | null;
    categoryNames: string[];
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
