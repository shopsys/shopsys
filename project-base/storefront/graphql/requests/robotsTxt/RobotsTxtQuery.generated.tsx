// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRobotsTxtQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeRobotsTxtQuery = { settings: { seo: { robotsTxtContent: string | null } } | null };


export const RobotsTxtQueryDocument = gql`
    query RobotsTxtQuery {
  settings {
    seo {
      robotsTxtContent
    }
  }
}
    `;

export function useRobotsTxtQuery(options?: Omit<Urql.UseQueryArgs<TypeRobotsTxtQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeRobotsTxtQuery, TypeRobotsTxtQueryVariables>({ query: RobotsTxtQueryDocument, ...options });
};