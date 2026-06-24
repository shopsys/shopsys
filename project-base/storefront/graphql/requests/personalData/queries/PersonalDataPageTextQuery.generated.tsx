// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypePersonalDataPageTextQueryVariables = Exact<{ [key: string]: never; }>;


export type TypePersonalDataPageTextQuery = { personalDataPage: { displaySiteContent: string | null, exportSiteContent: string | null } | null };


export const PersonalDataPageTextQueryDocument = gql`
    query PersonalDataPageTextQuery {
  personalDataPage {
    displaySiteContent
    exportSiteContent
  }
}
    `;

export function usePersonalDataPageTextQuery(options?: Omit<Urql.UseQueryArgs<TypePersonalDataPageTextQueryVariables>, 'query'>) {
  return Urql.useQuery<TypePersonalDataPageTextQuery, TypePersonalDataPageTextQueryVariables>({ query: PersonalDataPageTextQueryDocument, ...options });
};