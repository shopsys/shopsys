import { SimpleBrandType } from './brand';
import {
    AvailabilityFragmentApi,
    CartModificationsFragmentApi,
    ImageSizesFragmentApi,
    PriceFragmentApi,
    ProductPriceFragmentApi,
    SimplePaymentFragmentApi,
} from 'graphql/generated';
import { SimpleFlagType } from 'types/flag';
import { PickupPlaceType } from 'types/pickupPlace';
import { SimpleProductType } from 'types/product';
import { TransportType } from 'types/transport';
import { OperationContext } from 'urql';

export type CurrentCartType = {
    cart: CartType | null;
    isCartEmpty: boolean;
    transport: TransportType | null;
    pickupPlace: PickupPlaceType | null;
    payment: SimplePaymentFragmentApi | null;
    paymentGoPayBankSwift: string | null;
    promoCode: string | null;
    isLoading: boolean;
    isInitiallyLoaded: boolean;
    modifications: CartModificationsFragmentApi | null;
    refetchCart: (opts?: Partial<OperationContext> | undefined) => void;
};

export type ProductCartItemType = {
    id: number;
    uuid: string;
    slug: string;
    fullName: string;
    flags: SimpleFlagType[];
    image: ImageSizesFragmentApi | null;
    price: ProductPriceFragmentApi;
    availability: AvailabilityFragmentApi;
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
    totalPrice: PriceFragmentApi;
    totalItemsPrice: PriceFragmentApi;
    totalDiscountPrice: PriceFragmentApi;
    remainingAmountWithVatForFreeTransport: number | null;
};

export type AddToCartPopupDataType = SimpleProductType & {
    quantity: number;
};
