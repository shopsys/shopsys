import { SimpleBrandType } from './brand';
import {
    AvailabilityFragmentApi,
    CartModificationsFragmentApi,
    ImageSizesFragmentApi,
    ListedStoreFragmentApi,
    PriceFragmentApi,
    ProductPriceFragmentApi,
    SimplePaymentFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
import { SimpleFlagType } from 'types/flag';
import { SimpleProductType } from 'types/product';
import { OperationContext } from 'urql';

export type CurrentCartType = {
    cart: CartType | null;
    isCartEmpty: boolean;
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi | null;
    pickupPlace: ListedStoreFragmentApi | null;
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
