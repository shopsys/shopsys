// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ComplaintResolutionFragment } from '../fragments/ComplaintResolutionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeComplaintResolutionQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeComplaintResolutionQuery = { complaintResolution: Array<{ name: string, value: string }> };


export const ComplaintResolutionQueryDocument = gql`
    query ComplaintResolutionQuery {
  complaintResolution {
    ...ComplaintResolutionFragment
  }
}
    ${ComplaintResolutionFragment}`;

export function useComplaintResolutionQuery(options?: Omit<Urql.UseQueryArgs<TypeComplaintResolutionQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeComplaintResolutionQuery, TypeComplaintResolutionQueryVariables>({ query: ComplaintResolutionQueryDocument, ...options });
};