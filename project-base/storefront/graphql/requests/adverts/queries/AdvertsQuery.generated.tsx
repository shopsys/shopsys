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


export type TypeAdvertsQuery = (
  { __typename?: 'Query' }
  & { adverts: Array<(
    { __typename: 'AdvertCode' }
    & Pick<Types.TypeAdvertCode, 'code' | 'uuid' | 'name' | 'positionName' | 'type'>
    & { categories: Array<(
      { __typename: 'Category' }
      & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
    )> }
  ) | (
    { __typename: 'AdvertImage' }
    & Pick<Types.TypeAdvertImage, 'link' | 'uuid' | 'name' | 'positionName' | 'type'>
    & { mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, mainImageMobile: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, categories: Array<(
      { __typename: 'Category' }
      & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
    )> }
  )> }
);


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