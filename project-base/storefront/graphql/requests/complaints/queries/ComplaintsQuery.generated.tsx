// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PageInfoFragment } from '../../pageInfo/fragments/PageInfoFragment.generated';
import { ComplaintListItemFragment } from '../fragments/ComplaintListItemFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeComplaintsQueryVariables = Types.Exact<{
  first?: Types.InputMaybe<Types.Scalars['Int']['input']>;
  after?: Types.InputMaybe<Types.Scalars['String']['input']>;
  searchInput?: Types.InputMaybe<Types.TypeSearchInput>;
}>;


export type TypeComplaintsQuery = { __typename?: 'Query', complaints: { __typename?: 'ComplaintConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename?: 'ComplaintEdge', cursor: string, node: { __typename?: 'Complaint', uuid: string, number: string, createdAt: any, status: string, items: Array<{ __typename?: 'ComplaintItem', productName: string, product: { __typename?: 'MainVariant', slug: string, isVisible: boolean, mainImage: { __typename?: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', slug: string, isVisible: boolean, mainImage: { __typename?: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', slug: string, isVisible: boolean, mainImage: { __typename?: 'Image', name: string | null, url: string } | null } | null }> } | null } | null> | null } };


export const ComplaintsQueryDocument = gql`
    query ComplaintsQuery($first: Int, $after: String, $searchInput: SearchInput) {
  complaints(first: $first, after: $after, searchInput: $searchInput) {
    totalCount
    pageInfo {
      ...PageInfoFragment
    }
    edges {
      cursor
      node {
        ...ComplaintListItemFragment
      }
    }
  }
}
    ${PageInfoFragment}
${ComplaintListItemFragment}`;

export function useComplaintsQuery(options?: Omit<Urql.UseQueryArgs<TypeComplaintsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeComplaintsQuery, TypeComplaintsQueryVariables>({ query: ComplaintsQueryDocument, ...options });
};