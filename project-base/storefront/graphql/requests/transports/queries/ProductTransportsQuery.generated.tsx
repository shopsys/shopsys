// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductDetailTransportFragment } from '../fragments/ProductDetailTransportFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

export type TypeProductTransportsQueryVariables = Exact<{
  productUuid: string;
}>;


export type TypeProductTransportsQuery = { transports: Array<{ __typename: 'Transport', uuid: string, name: string, description: string | null, expectedDeliveryDate: string | null, transportTypeCode: Types.TypeTransportTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null }> };


export const ProductTransportsQueryDocument = gql`
    query ProductTransportsQuery($productUuid: Uuid!) {
  transports(productUuid: $productUuid) {
    ...ProductDetailTransportFragment
  }
}
    ${ProductDetailTransportFragment}`;

export function useProductTransportsQuery(options: Omit<Urql.UseQueryArgs<TypeProductTransportsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeProductTransportsQuery, TypeProductTransportsQueryVariables>({ query: ProductTransportsQueryDocument, ...options });
};