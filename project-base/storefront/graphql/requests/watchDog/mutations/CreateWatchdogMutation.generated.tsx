// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCreateWatchdogInput = {
  /** The customer's email address */
  email: string;
  /** Product UUID */
  productUuid: string;
};

export type TypeCreateWatchdogMutationVariables = Exact<{
  input: Types.TypeCreateWatchdogInput;
}>;


export type TypeCreateWatchdogMutation = { CreateWatchdog: boolean };


export const CreateWatchdogMutationDocument = gql`
    mutation CreateWatchdogMutation($input: CreateWatchdogInput!) {
  CreateWatchdog(input: $input)
}
    `;

export function useCreateWatchdogMutation() {
  return Urql.useMutation<TypeCreateWatchdogMutation, TypeCreateWatchdogMutationVariables>(CreateWatchdogMutationDocument);
};