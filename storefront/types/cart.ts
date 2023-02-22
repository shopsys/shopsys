import {
    CartItemFragmentApi,
    CartModificationsFragmentApi,
    ListedStoreFragmentApi,
    PriceFragmentApi,
    SimplePaymentFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
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

export type CartType = {
    items: CartItemFragmentApi[];
    totalPrice: PriceFragmentApi;
    totalItemsPrice: PriceFragmentApi;
    totalDiscountPrice: PriceFragmentApi;
    remainingAmountWithVatForFreeTransport: number | null;
};
