// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { DeliveryAddressFragment } from '../fragments/DeliveryAddressFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCreateDeliveryAddressMutationVariables = Types.Exact<{
  input: Types.TypeDeliveryAddressInput;
}>;


export type TypeCreateDeliveryAddressMutation = { __typename?: 'Mutation', CreateDeliveryAddress: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> };


export const CreateDeliveryAddressMutationDocument = gql`
    mutation CreateDeliveryAddressMutation($input: DeliveryAddressInput!) {
  CreateDeliveryAddress(input: $input) {
    ...DeliveryAddressFragment
  }
}
    ${DeliveryAddressFragment}`;

export function useCreateDeliveryAddressMutation() {
  return Urql.useMutation<TypeCreateDeliveryAddressMutation, TypeCreateDeliveryAddressMutationVariables>(CreateDeliveryAddressMutationDocument);
};