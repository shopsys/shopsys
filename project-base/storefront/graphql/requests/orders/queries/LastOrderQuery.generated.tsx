// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { LastOrderFragment } from '../fragments/LastOrderFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeLastOrderQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeLastOrderQuery = (
  { __typename?: 'Query' }
  & { lastOrder: Types.Maybe<(
    { __typename: 'Order' }
    & Pick<Types.TypeOrder, 'pickupPlaceIdentifier' | 'deliveryStreet' | 'deliveryCity' | 'deliveryPostcode'>
    & { deliveryCountry: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )>, items: Array<(
      { __typename?: 'OrderItem' }
      & Pick<Types.TypeOrderItem, 'type'>
      & { payment: Types.Maybe<(
        { __typename?: 'Payment' }
        & Pick<Types.TypePayment, 'uuid'>
      )>, transport: Types.Maybe<(
        { __typename?: 'Transport' }
        & Pick<Types.TypeTransport, 'uuid' | 'transportTypeCode'>
      )> }
    )> }
  )> }
);


export const LastOrderQueryDocument = gql`
    query LastOrderQuery {
  lastOrder {
    ...LastOrderFragment
  }
}
    ${LastOrderFragment}`;

export function useLastOrderQuery(options?: Omit<Urql.UseQueryArgs<TypeLastOrderQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeLastOrderQuery, TypeLastOrderQueryVariables>({ query: LastOrderQueryDocument, ...options });
};