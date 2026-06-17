// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { LastOrderFragment } from '../fragments/LastOrderFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

export type TypeLastOrderQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeLastOrderQuery = { lastOrder: { __typename: 'Order', pickupPlaceIdentifier: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, deliveryCountry: { __typename: 'Country', name: string, code: string } | null, items: Array<{ type: Types.TypeOrderItemTypeEnum, payment: { uuid: string } | null, transport: { uuid: string, transportTypeCode: Types.TypeTransportTypeEnum } | null }> } | null };


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