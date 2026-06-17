// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of two possible types for personal data access request */
export type TypePersonalDataAccessRequestTypeEnum =
  /** Display data */
  | 'display'
  /** Export data */
  | 'export';

export type TypePersonalDataRequestMutationVariables = Exact<{
  email: string;
  type?: Types.TypePersonalDataAccessRequestTypeEnum | null | undefined;
}>;


export type TypePersonalDataRequestMutation = { RequestPersonalDataAccess: { displaySiteSlug: string, exportSiteSlug: string } };


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