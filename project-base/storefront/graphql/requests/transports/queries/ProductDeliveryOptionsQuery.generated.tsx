// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductDeliveryOptionFragment } from '../fragments/ProductDeliveryOptionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

export type TypeProductDeliveryOptionsQueryVariables = Exact<{
  productUuid: string;
}>;


export type TypeProductDeliveryOptionsQuery = { productDeliveryOptions: Array<{ __typename: 'ProductDeliveryOption', expectedDeliveryDate: string | null, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, transportTypeCode: Types.TypeTransportTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null }, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }> };


export const ProductDeliveryOptionsQueryDocument = gql`
    query ProductDeliveryOptionsQuery($productUuid: Uuid!) {
  productDeliveryOptions(productUuid: $productUuid) {
    ...ProductDeliveryOptionFragment
  }
}
    ${ProductDeliveryOptionFragment}`;

export function useProductDeliveryOptionsQuery(options: Omit<Urql.UseQueryArgs<TypeProductDeliveryOptionsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeProductDeliveryOptionsQuery, TypeProductDeliveryOptionsQueryVariables>({ query: ProductDeliveryOptionsQueryDocument, ...options });
};