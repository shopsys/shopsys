import { CartFragmentApi } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { CartModificationsFragmentApi } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { SimplePaymentFragmentApi } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { ListedStoreFragmentApi } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { Maybe } from 'graphql/requests/types';

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
};
