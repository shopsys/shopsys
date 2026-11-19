import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypePromoCodeFragment } from 'graphql/requests/cart/fragments/PromoCodeFragment.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { Maybe, TypeAppliedGiftVoucher } from 'graphql/types';
import { UseQueryExecute } from 'urql';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export type AppliedGiftVoucherType = Pick<TypeAppliedGiftVoucher, 'code' | 'valueWithVat'>;

export type CurrentCartType = {
    cart: Maybe<TypeCartFragment> | undefined;
    transport: Maybe<TypeTransportWithAvailablePaymentsAndStoresFragment>;
    pickupPlace: Maybe<StoreOrPacketeryPoint>;
    payment: Maybe<TypeSimplePaymentFragment>;
    paymentGoPayBankSwift: Maybe<string>;
    promoCodes: TypePromoCodeFragment[];
    giftVouchers: AppliedGiftVoucherType[];
    isCartFetchingOrUnavailable: boolean;
    roundingPrice: Maybe<TypePriceFragment>;
    fetchCart: UseQueryExecute;
};
