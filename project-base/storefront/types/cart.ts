import {
    CartFragmentApi,
    CartModificationsFragmentApi,
    ListedStoreFragmentApi,
    Maybe,
    PriceFragmentApi,
    SimplePaymentFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
import { UseQueryExecute } from 'urql';

export type CurrentCartType = {
    cart: Maybe<CartFragmentApi>;
    transport: Maybe<TransportWithAvailablePaymentsAndStoresFragmentApi>;
    pickupPlace: Maybe<ListedStoreFragmentApi>;
    payment: Maybe<SimplePaymentFragmentApi>;
    paymentGoPayBankSwift: Maybe<string>;
    promoCode: Maybe<string>;
    isLoading: boolean;
    isFetching: boolean;
    modifications: Maybe<CartModificationsFragmentApi>;
    roundingPrice: Maybe<PriceFragmentApi>;
    refetchCart: UseQueryExecute;
};
