import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { Maybe, TypePromoCode } from 'graphql/types';
import { UseQueryExecute } from 'urql';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export type CurrentCartType = {
    cart: Maybe<TypeCartFragment> | undefined;
    transport: Maybe<TypeTransportWithAvailablePaymentsAndStoresFragment>;
    pickupPlace: Maybe<StoreOrPacketeryPoint>;
    payment: Maybe<TypeSimplePaymentFragment>;
    paymentGoPayBankSwift: Maybe<string>;
    promoCodes: TypePromoCode[];
    isCartFetchingOrUnavailable: boolean;
    modifications: Maybe<TypeCartModificationsFragment>;
    roundingPrice: Maybe<TypePriceFragment>;
    fetchCart: UseQueryExecute;
};
