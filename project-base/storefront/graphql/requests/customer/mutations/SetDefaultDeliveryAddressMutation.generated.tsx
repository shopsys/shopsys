// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { DeliveryAddressFragment } from '../fragments/DeliveryAddressFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSetDefaultDeliveryAddressMutationVariables = Types.Exact<{
  deliveryAddressUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeSetDefaultDeliveryAddressMutation = { __typename?: 'Mutation', SetDefaultDeliveryAddress: { __typename?: 'CurrentCompanyCustomerUser', uuid: string, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null } | { __typename?: 'CurrentRegularCustomerUser', uuid: string, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null } };


export const SetDefaultDeliveryAddressMutationDocument = gql`
    mutation SetDefaultDeliveryAddressMutation($deliveryAddressUuid: Uuid!) {
  SetDefaultDeliveryAddress(deliveryAddressUuid: $deliveryAddressUuid) {
    uuid
    defaultDeliveryAddress {
      ...DeliveryAddressFragment
    }
  }
}
    ${DeliveryAddressFragment}`;

export function useSetDefaultDeliveryAddressMutation() {
  return Urql.useMutation<TypeSetDefaultDeliveryAddressMutation, TypeSetDefaultDeliveryAddressMutationVariables>(SetDefaultDeliveryAddressMutationDocument);
};