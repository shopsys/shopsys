// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { DeliveryAddressFragment } from '../fragments/DeliveryAddressFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeDeleteDeliveryAddressMutationVariables = Types.Exact<{
  deliveryAddressUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeDeleteDeliveryAddressMutation = { __typename?: 'Mutation', DeleteDeliveryAddress: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> };


export const DeleteDeliveryAddressMutationDocument = gql`
    mutation DeleteDeliveryAddressMutation($deliveryAddressUuid: Uuid!) {
  DeleteDeliveryAddress(deliveryAddressUuid: $deliveryAddressUuid) {
    ...DeliveryAddressFragment
  }
}
    ${DeliveryAddressFragment}`;

export function useDeleteDeliveryAddressMutation() {
  return Urql.useMutation<TypeDeleteDeliveryAddressMutation, TypeDeleteDeliveryAddressMutationVariables>(DeleteDeliveryAddressMutationDocument);
};