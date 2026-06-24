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
  filter?: Types.InputMaybe<Types.TypeComplaintFilterInput>;
  statuslessFilter?: Types.InputMaybe<Types.TypeComplaintFilterInput>;
}>;


export type TypeComplaintsQuery = { __typename?: 'Query', complaints: { __typename?: 'ComplaintConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename?: 'ComplaintEdge', cursor: string, node: { __typename?: 'Complaint', uuid: string, number: string, createdAt: any, status: string, resolution: { __typename?: 'ComplaintResolution', name: string }, items: Array<{ __typename?: 'ComplaintItem', uuid: string, quantity: number, productName: string, product: { __typename?: 'MainVariant', slug: string, isVisible: boolean, mainImage: { __typename?: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', slug: string, isVisible: boolean, mainImage: { __typename?: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', slug: string, isVisible: boolean, mainImage: { __typename?: 'Image', name: string | null, url: string } | null } | null }> } | null } | null> | null }, complaintStatusCounts: Array<{ __typename?: 'ComplaintStatusCount', count: number, status: { __typename?: 'ComplaintStatus', code: string, type: Types.TypeComplaintStatusEnum, name: string } }> };


export const ComplaintsQueryDocument = gql`
    query ComplaintsQuery($first: Int, $after: String, $filter: ComplaintFilterInput, $statuslessFilter: ComplaintFilterInput) {
  complaints(first: $first, after: $after, filter: $filter) {
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
  complaintStatusCounts(filter: $statuslessFilter) {
    status {
      code
      type
      name
    }
    count
  }
}
    ${PageInfoFragment}
${ComplaintListItemFragment}`;

export function useComplaintsQuery(options?: Omit<Urql.UseQueryArgs<TypeComplaintsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeComplaintsQuery, TypeComplaintsQueryVariables>({ query: ComplaintsQueryDocument, ...options });
};