// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { DeliveryAddressFragment } from '../fragments/DeliveryAddressFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSetDefaultDeliveryAddressMutationVariables = Exact<{
  deliveryAddressUuid: string;
}>;


export type TypeSetDefaultDeliveryAddressMutation = { SetDefaultDeliveryAddress:
    | { uuid: string, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null } | null }
    | { uuid: string, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null } | null }
   };


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