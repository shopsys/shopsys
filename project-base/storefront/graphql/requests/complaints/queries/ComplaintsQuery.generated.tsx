// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PageInfoFragment } from '../../pageInfo/fragments/PageInfoFragment.generated';
import { ComplaintListItemFragment } from '../fragments/ComplaintListItemFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Filter complaints */
export type TypeComplaintFilterInput = {
  /** Filter complaints created after this date */
  createdAfter?: string | null | undefined;
  /** Filter complaints created before this date */
  createdBefore?: string | null | undefined;
  /** Filter complaints by complaint number or product */
  search?: string | null | undefined;
  /** Filter complaints by status codes */
  statusCodes?: Array<string> | null | undefined;
};

/** Status of complaint */
export type TypeComplaintStatusEnum =
  /** In progress */
  | 'in_progress'
  /** New */
  | 'new'
  /** Resolved */
  | 'resolved';

export type TypeComplaintsQueryVariables = Exact<{
  first?: number | null | undefined;
  after?: string | null | undefined;
  filter?: Types.TypeComplaintFilterInput | null | undefined;
  statuslessFilter?: Types.TypeComplaintFilterInput | null | undefined;
}>;


export type TypeComplaintsQuery = { complaints: { totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ cursor: string, node: { uuid: string, number: string, createdAt: string, status: string, resolution: { name: string }, items: Array<{ uuid: string, quantity: number, productName: string, product:
            | { slug: string, isVisible: boolean, mainImage: { name: string | null, url: string } | null }
            | { slug: string, isVisible: boolean, mainImage: { name: string | null, url: string } | null }
            | { slug: string, isVisible: boolean, mainImage: { name: string | null, url: string } | null }
           | null }> } | null } | null> | null }, complaintStatusCounts: Array<{ count: number, status: { code: string, type: Types.TypeComplaintStatusEnum, name: string } }> };


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