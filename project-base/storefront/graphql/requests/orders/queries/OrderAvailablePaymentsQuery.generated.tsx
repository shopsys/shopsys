// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimplePaymentFragment } from '../../payments/fragments/SimplePaymentFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of the possible methods of the payment type */
export type TypePaymentTypeEnum =
  | 'bankTransfer'
  | 'basic'
  | 'goPay';

export type TypeOrderAvailablePaymentsQueryVariables = Exact<{
  orderUuid: string;
  orderUrlHash?: string | null | undefined;
}>;


export type TypeOrderAvailablePaymentsQuery = { orderPayments: { availablePayments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, currentPayment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null } };


export const OrderAvailablePaymentsQueryDocument = gql`
    query OrderAvailablePaymentsQuery($orderUuid: Uuid!, $orderUrlHash: String) {
  orderPayments(orderUuid: $orderUuid, orderUrlHash: $orderUrlHash) {
    availablePayments {
      ...SimplePaymentFragment
    }
    currentPayment {
      ...SimplePaymentFragment
    }
  }
}
    ${SimplePaymentFragment}`;

export function useOrderAvailablePaymentsQuery(options: Omit<Urql.UseQueryArgs<TypeOrderAvailablePaymentsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderAvailablePaymentsQuery, TypeOrderAvailablePaymentsQueryVariables>({ query: OrderAvailablePaymentsQueryDocument, ...options });
};