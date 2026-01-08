// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypePersonalDataPageTextQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypePersonalDataPageTextQuery = { __typename?: 'Query', personalDataPage: { __typename?: 'PersonalDataPage', displaySiteContent: string | null, exportSiteContent: string | null } | null };


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