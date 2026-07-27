// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeProductListItemImagesQueryVariables = Exact<{
  uuid: string;
}>;


export type TypeProductListItemImagesQuery = { product:
    | { images: Array<{ __typename: 'Image', name: string | null, url: string }> }
    | { images: Array<{ __typename: 'Image', name: string | null, url: string }> }
    | { images: Array<{ __typename: 'Image', name: string | null, url: string }> }
   | null };


export const ProductListItemImagesQueryDocument = gql`
    query ProductListItemImagesQuery($uuid: Uuid!) {
  product(uuid: $uuid) {
    images {
      ...ImageFragment
    }
  }
}
    ${ImageFragment}`;

export function useProductListItemImagesQuery(options: Omit<Urql.UseQueryArgs<TypeProductListItemImagesQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeProductListItemImagesQuery, TypeProductListItemImagesQueryVariables>({ query: ProductListItemImagesQueryDocument, ...options });
};