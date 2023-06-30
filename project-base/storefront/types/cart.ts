import {
    CartFragmentApi,
    CartModificationsFragmentApi,
    ListedStoreFragmentApi,
    Maybe,
    SimplePaymentFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
import { OperationContext } from 'urql';

export type CurrentCartType = {
    cart: Maybe<CartFragmentApi>;
    isCartEmpty: boolean;
    transport: Maybe<TransportWithAvailablePaymentsAndStoresFragmentApi>;
    pickupPlace: Maybe<ListedStoreFragmentApi>;
    payment: Maybe<SimplePaymentFragmentApi>;
    paymentGoPayBankSwift: Maybe<string>;
    promoCode: Maybe<string>;
    isLoading: boolean;
    isFetching: boolean;
    modifications: Maybe<CartModificationsFragmentApi>;
    refetchCart: (opts?: Partial<OperationContext>) => void;
};
