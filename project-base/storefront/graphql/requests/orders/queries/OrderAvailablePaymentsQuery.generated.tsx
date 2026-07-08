// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimplePaymentFragment } from '../../payments/fragments/SimplePaymentFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderAvailablePaymentsQueryVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeOrderAvailablePaymentsQuery = (
  { __typename?: 'Query' }
  & { orderPayments: (
    { __typename?: 'OrderPaymentsConfig' }
    & { availablePayments: Array<(
      { __typename: 'Payment' }
      & Pick<Types.TypePayment, 'uuid' | 'name' | 'description' | 'instructions' | 'type'>
      & { price: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
      ), mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, goPayPaymentMethod: Types.Maybe<(
        { __typename: 'GoPayPaymentMethod' }
        & Pick<Types.TypeGoPayPaymentMethod, 'identifier' | 'name' | 'paymentGroup'>
      )> }
    )>, currentPayment: Types.Maybe<(
      { __typename: 'Payment' }
      & Pick<Types.TypePayment, 'uuid' | 'name' | 'description' | 'instructions' | 'type'>
      & { price: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
      ), mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, goPayPaymentMethod: Types.Maybe<(
        { __typename: 'GoPayPaymentMethod' }
        & Pick<Types.TypeGoPayPaymentMethod, 'identifier' | 'name' | 'paymentGroup'>
      )> }
    )> }
  ) }
);


export const OrderAvailablePaymentsQueryDocument = gql`
    query OrderAvailablePaymentsQuery($orderUuid: Uuid!) {
  orderPayments(orderUuid: $orderUuid) {
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