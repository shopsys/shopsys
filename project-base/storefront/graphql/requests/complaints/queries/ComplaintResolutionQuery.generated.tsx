// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ComplaintResolutionFragment } from '../fragments/ComplaintResolutionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeComplaintResolutionQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeComplaintResolutionQuery = (
  { __typename?: 'Query' }
  & { complaintResolution: Array<(
    { __typename?: 'ComplaintResolution' }
    & Pick<Types.TypeComplaintResolution, 'name' | 'value'>
  )> }
);


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