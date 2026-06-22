// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TransportWithAvailablePaymentsFragment } from '../fragments/TransportWithAvailablePaymentsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of the possible methods of the payment type */
export type TypePaymentTypeEnum =
  | 'bankTransfer'
  | 'basic'
  | 'goPay';

/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

/** Reason why a transport cannot be selected for the given cart */
export type TypeTransportUnavailabilityReasonInCartEnum =
  | 'excluded_for_product'
  | 'personal_pickup_required';

export type TypeTransportsQueryVariables = Exact<{
  cartUuid?: string | null | undefined;
}>;


export type TypeTransportsQuery = { transports: Array<{ __typename: 'Transport', uuid: string, name: string, description: string | null, daysUntilDelivery: number, transportTypeCode: Types.TypeTransportTypeEnum, isPersonalPickup: boolean, vatPercent: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, productsBlockingSelectionInCart: Array<{ reason: Types.TypeTransportUnavailabilityReasonInCartEnum, products: Array<
        | { uuid: string, fullName: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
        | { uuid: string, fullName: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
        | { uuid: string, fullName: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
      > }> }> };


export const TransportsQueryDocument = gql`
    query TransportsQuery($cartUuid: Uuid) {
  transports(cartUuid: $cartUuid) {
    ...TransportWithAvailablePaymentsFragment
  }
}
    ${TransportWithAvailablePaymentsFragment}`;

export function useTransportsQuery(options?: Omit<Urql.UseQueryArgs<TypeTransportsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeTransportsQuery, TypeTransportsQueryVariables>({ query: TransportsQueryDocument, ...options });
};