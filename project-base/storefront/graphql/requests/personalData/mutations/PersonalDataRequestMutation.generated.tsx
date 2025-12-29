// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypePersonalDataRequestMutationVariables = Types.Exact<{
  email: Types.Scalars['String']['input'];
  type?: Types.InputMaybe<Types.TypePersonalDataAccessRequestTypeEnum>;
}>;


export type TypePersonalDataRequestMutation = { __typename?: 'Mutation', RequestPersonalDataAccess: { __typename?: 'PersonalDataPage', displaySiteSlug: string, exportSiteSlug: string } };


export const PersonalDataRequestMutationDocument = gql`
    mutation PersonalDataRequestMutation($email: String!, $type: PersonalDataAccessRequestTypeEnum) {
  RequestPersonalDataAccess(input: {email: $email, type: $type}) {
    displaySiteSlug
    exportSiteSlug
  }
}
    `;

export function usePersonalDataRequestMutation() {
  return Urql.useMutation<TypePersonalDataRequestMutation, TypePersonalDataRequestMutationVariables>(PersonalDataRequestMutationDocument);
};