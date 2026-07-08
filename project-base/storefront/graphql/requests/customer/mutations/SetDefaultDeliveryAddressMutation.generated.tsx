// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { DeliveryAddressFragment } from '../fragments/DeliveryAddressFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSetDefaultDeliveryAddressMutationVariables = Types.Exact<{
  deliveryAddressUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeSetDefaultDeliveryAddressMutation = (
  { __typename?: 'Mutation' }
  & { SetDefaultDeliveryAddress: (
    { __typename?: 'CurrentCompanyCustomerUser' }
    & Pick<Types.TypeCurrentCompanyCustomerUser, 'uuid'>
    & { defaultDeliveryAddress: Types.Maybe<(
      { __typename: 'DeliveryAddress' }
      & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
      & { telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )>, country: Types.Maybe<(
        { __typename: 'Country' }
        & Pick<Types.TypeCountry, 'name' | 'code'>
      )> }
    )> }
  ) | (
    { __typename?: 'CurrentRegularCustomerUser' }
    & Pick<Types.TypeCurrentRegularCustomerUser, 'uuid'>
    & { defaultDeliveryAddress: Types.Maybe<(
      { __typename: 'DeliveryAddress' }
      & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
      & { telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )>, country: Types.Maybe<(
        { __typename: 'Country' }
        & Pick<Types.TypeCountry, 'name' | 'code'>
      )> }
    )> }
  ) }
);


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