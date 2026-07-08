// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TransportWithAvailablePaymentsFragment } from '../fragments/TransportWithAvailablePaymentsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeTransportsQueryVariables = Types.Exact<{
  cartUuid?: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;


export type TypeTransportsQuery = (
  { __typename?: 'Query' }
  & { transports: Array<(
    { __typename: 'Transport' }
    & Pick<Types.TypeTransport, 'uuid' | 'name' | 'description' | 'daysUntilDelivery' | 'transportTypeCode' | 'isPersonalPickup' | 'vatPercent'>
    & { price: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ), mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, payments: Array<(
      { __typename: 'Payment' }
      & Pick<Types.TypePayment, 'uuid' | 'name' | 'description' | 'instructions' | 'type'>
      & { price: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
      ), mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, goPayPaymentMethod: Types.Maybe<(
        { __typename: 'GoPayPaymentMethod' }
        & Pick<Types.TypeGoPayPaymentMethod, 'identifier' | 'name' | 'paymentGroup'>
      )> }
    )> }
  )> }
);


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