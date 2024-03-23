import { CartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { CartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { SimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { PriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { ListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { Maybe } from 'graphql/types';
import { UseQueryExecute } from 'urql';

export type CurrentCartType = {
    cart: Maybe<CartFragment> | undefined;
    isWithCart: boolean;
    transport: Maybe<TransportWithAvailablePaymentsAndStoresFragment>;
    pickupPlace: Maybe<ListedStoreFragment>;
    payment: Maybe<SimplePaymentFragment>;
    paymentGoPayBankSwift: Maybe<string>;
    promoCode: Maybe<string>;
    isFetching: boolean;
    modifications: Maybe<CartModificationsFragment>;
    roundingPrice: Maybe<PriceFragment>;
    fetchCart: UseQueryExecute;
};
