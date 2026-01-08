// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TransportWithAvailablePaymentsFragment } from '../fragments/TransportWithAvailablePaymentsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeTransportsQueryVariables = Types.Exact<{
  cartUuid?: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;


export type TypeTransportsQuery = { __typename?: 'Query', transports: Array<{ __typename: 'Transport', uuid: string, name: string, description: string | null, daysUntilDelivery: number, transportTypeCode: Types.TypeTransportTypeEnum, isPersonalPickup: boolean, vatPercent: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }> }> };


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