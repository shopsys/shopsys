// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { AdvertsFragment } from '../fragments/AdvertsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeAdvertsQueryVariables = Types.Exact<{
  categoryUuid?: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
  positionNames?: Types.InputMaybe<Array<Types.Scalars['String']['input']> | Types.Scalars['String']['input']>;
}>;


export type TypeAdvertsQuery = { __typename?: 'Query', adverts: Array<{ __typename: 'AdvertCode', code: string, uuid: string, name: string, positionName: string, type: string, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> } | { __typename: 'AdvertImage', link: string | null, uuid: string, name: string, positionName: string, type: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, mainImageMobile: { __typename: 'Image', name: string | null, url: string } | null, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> }> };


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