// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCatalogCategoriesQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeCatalogCategoriesQuery = { __typename?: 'Query', categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, children: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> }> };


export const CatalogCategoriesQueryDocument = gql`
    query CatalogCategoriesQuery {
  categories {
    __typename
    uuid
    name
    slug
    mainImage {
      ...ImageFragment
    }
    children {
      __typename
      uuid
      name
      slug
    }
  }
}
    ${ImageFragment}`;

export function useCatalogCategoriesQuery(options?: Omit<Urql.UseQueryArgs<TypeCatalogCategoriesQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCatalogCategoriesQuery, TypeCatalogCategoriesQueryVariables>({ query: CatalogCategoriesQueryDocument, ...options });
};