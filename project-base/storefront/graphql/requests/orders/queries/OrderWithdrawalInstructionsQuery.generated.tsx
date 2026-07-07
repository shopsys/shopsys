// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderWithdrawalInstructionsFragment } from '../fragments/OrderWithdrawalInstructionsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderWithdrawalInstructionsQueryVariables = Exact<{
  urlHash: string;
}>;


export type TypeOrderWithdrawalInstructionsQuery = { order: { __typename: 'Order', withdrawalInstructions: string } | null };


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