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


export type TypeComplaintsQuery = (
  { __typename?: 'Query' }
  & { complaints: (
    { __typename?: 'ComplaintConnection' }
    & Pick<Types.TypeComplaintConnection, 'totalCount'>
    & { pageInfo: (
      { __typename: 'PageInfo' }
      & Pick<Types.TypePageInfo, 'hasNextPage' | 'hasPreviousPage' | 'endCursor'>
    ), edges: Types.Maybe<Array<Types.Maybe<(
      { __typename?: 'ComplaintEdge' }
      & Pick<Types.TypeComplaintEdge, 'cursor'>
      & { node: Types.Maybe<(
        { __typename?: 'Complaint' }
        & Pick<Types.TypeComplaint, 'uuid' | 'number' | 'createdAt' | 'status'>
        & { resolution: (
          { __typename?: 'ComplaintResolution' }
          & Pick<Types.TypeComplaintResolution, 'name'>
        ), items: Array<(
          { __typename?: 'ComplaintItem' }
          & Pick<Types.TypeComplaintItem, 'uuid' | 'quantity' | 'productName'>
          & { product: Types.Maybe<(
            { __typename?: 'MainVariant' }
            & Pick<Types.TypeMainVariant, 'slug' | 'isVisible'>
            & { mainImage: Types.Maybe<(
              { __typename?: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )> }
          ) | (
            { __typename?: 'RegularProduct' }
            & Pick<Types.TypeRegularProduct, 'slug' | 'isVisible'>
            & { mainImage: Types.Maybe<(
              { __typename?: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )> }
          ) | (
            { __typename?: 'Variant' }
            & Pick<Types.TypeVariant, 'slug' | 'isVisible'>
            & { mainImage: Types.Maybe<(
              { __typename?: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )> }
          )> }
        )> }
      )> }
    )>>> }
  ), complaintStatusCounts: Array<(
    { __typename?: 'ComplaintStatusCount' }
    & Pick<Types.TypeComplaintStatusCount, 'count'>
    & { status: (
      { __typename?: 'ComplaintStatus' }
      & Pick<Types.TypeComplaintStatus, 'code' | 'type' | 'name'>
    ) }
  )> }
);


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