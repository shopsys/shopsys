// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Status of order */
export type TypeOrderStatusEnum =
  /** Canceled */
  | 'canceled'
  /** Done */
  | 'done'
  /** In progress */
  | 'inProgress'
  /** New */
  | 'new'
  /** Withdrawn */
  | 'withdrawn';

export type TypeDashboardOrdersQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeDashboardOrdersQuery = { orders: { edges: Array<{ node: { uuid: string, number: string, status: string, statusType: Types.TypeOrderStatusEnum, date: string } | null } | null> | null } | null, orderStatusCounts: Array<{ count: number, status: { code: string, type: Types.TypeOrderStatusEnum } }> };


export const DashboardOrdersQueryDocument = gql`
    query DashboardOrdersQuery {
  orders(first: 3) {
    edges {
      node {
        uuid
        number
        date: creationDate
        status
        statusType
      }
    }
  }
  orderStatusCounts {
    status {
      code
      type
    }
    count
  }
}
    `;

export function useDashboardOrdersQuery(options?: Omit<Urql.UseQueryArgs<TypeDashboardOrdersQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeDashboardOrdersQuery, TypeDashboardOrdersQueryVariables>({ query: DashboardOrdersQueryDocument, ...options });
};