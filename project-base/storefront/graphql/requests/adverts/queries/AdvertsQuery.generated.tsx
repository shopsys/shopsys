// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { AdvertsFragment } from '../fragments/AdvertsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeAdvertsQueryVariables = Exact<{
  categoryUuid?: string | null | undefined;
  positionNames?: Array<string> | string | null | undefined;
}>;


export type TypeAdvertsQuery = { adverts: Array<
    | { __typename: 'AdvertCode', code: string, uuid: string, name: string, positionName: string, type: string, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> }
    | { __typename: 'AdvertImage', link: string | null, uuid: string, name: string, positionName: string, type: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, mainImageMobile: { __typename: 'Image', name: string | null, url: string } | null, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> }
  > };


export const AdvertsQueryDocument = gql`
    query AdvertsQuery($categoryUuid: Uuid, $positionNames: [String!]) @redisCache(ttl: 3600) {
  adverts(categoryUuid: $categoryUuid, positionNames: $positionNames) {
    ...AdvertsFragment
  }
}
    ${AdvertsFragment}`;

export function useAdvertsQuery(options?: Omit<Urql.UseQueryArgs<TypeAdvertsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeAdvertsQuery, TypeAdvertsQueryVariables>({ query: AdvertsQueryDocument, ...options });
};