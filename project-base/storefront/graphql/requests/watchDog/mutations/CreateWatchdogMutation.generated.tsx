// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCreateWatchdogMutationVariables = Types.Exact<{
  input: Types.TypeCreateWatchdogInput;
}>;


export type TypeCreateWatchdogMutation = { __typename?: 'Mutation', CreateWatchdog: boolean };


export const CreateWatchdogMutationDocument = gql`
    mutation CreateWatchdogMutation($input: CreateWatchdogInput!) {
  CreateWatchdog(input: $input)
}
    `;

export function useCreateWatchdogMutation() {
  return Urql.useMutation<TypeCreateWatchdogMutation, TypeCreateWatchdogMutationVariables>(CreateWatchdogMutationDocument);
};