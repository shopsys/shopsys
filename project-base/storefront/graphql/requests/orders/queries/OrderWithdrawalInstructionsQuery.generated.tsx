// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderWithdrawalInstructionsFragment } from '../fragments/OrderWithdrawalInstructionsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderWithdrawalInstructionsQueryVariables = Types.Exact<{
  urlHash: Types.Scalars['String']['input'];
}>;


export type TypeOrderWithdrawalInstructionsQuery = { __typename?: 'Query', order: { __typename: 'Order', withdrawalInstructions: string } | null };


export const OrderWithdrawalInstructionsQueryDocument = gql`
    query OrderWithdrawalInstructionsQuery($urlHash: String!) {
  order(urlHash: $urlHash) {
    ...OrderWithdrawalInstructionsFragment
  }
}
    ${OrderWithdrawalInstructionsFragment}`;

export function useOrderWithdrawalInstructionsQuery(options: Omit<Urql.UseQueryArgs<TypeOrderWithdrawalInstructionsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderWithdrawalInstructionsQuery, TypeOrderWithdrawalInstructionsQueryVariables>({ query: OrderWithdrawalInstructionsQueryDocument, ...options });
};