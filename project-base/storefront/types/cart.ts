import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { Maybe } from 'graphql/types';
import { UseQueryExecute } from 'urql';

export type CurrentCartType = {
    cart: Maybe<TypeCartFragment> | undefined;
    isWithCart: boolean;
    transport: Maybe<TypeTransportWithAvailablePaymentsAndStoresFragment>;
    pickupPlace: Maybe<TypeListedStoreFragment>;
    payment: Maybe<TypeSimplePaymentFragment>;
    paymentGoPayBankSwift: Maybe<string>;
    promoCode: Maybe<string>;
    isFetching: boolean;
    modifications: Maybe<TypeCartModificationsFragment>;
    roundingPrice: Maybe<TypePriceFragment>;
    fetchCart: UseQueryExecute;
};
