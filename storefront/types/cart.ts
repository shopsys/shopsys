import {
    CartFragmentApi,
    CartModificationsFragmentApi,
    ListedStoreFragmentApi,
    SimplePaymentFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
import { OperationContext } from 'urql';

export type CurrentCartType = {
    cart: CartFragmentApi | null;
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
