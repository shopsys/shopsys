// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Status of complaint */
export type TypeComplaintStatusEnum =
  /** In progress */
  | 'in_progress'
  /** New */
  | 'new'
  /** Resolved */
  | 'resolved';

export type TypeDashboardComplaintsQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeDashboardComplaintsQuery = { complaints: { edges: Array<{ node: { number: string, status: string, date: string } | null } | null> | null }, complaintStatusCounts: Array<{ count: number, status: { code: string, type: Types.TypeComplaintStatusEnum } }> };


export const DashboardComplaintsQueryDocument = gql`
    query DashboardComplaintsQuery {
  complaints(first: 3) {
    edges {
      node {
        number
        date: createdAt
        status
      }
    }
  }
  complaintStatusCounts {
    status {
      code
      type
    }
    count
  }
}
    `;

export function useDashboardComplaintsQuery(options?: Omit<Urql.UseQueryArgs<TypeDashboardComplaintsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeDashboardComplaintsQuery, TypeDashboardComplaintsQueryVariables>({ query: DashboardComplaintsQueryDocument, ...options });
};